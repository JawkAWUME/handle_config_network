<?php
// app/Repositories/FirewallRepository.php

namespace App\Repositories;

use App\Repositories\Contracts\FirewallRepositoryInterface;
use App\Models\Firewall;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FirewallRepository implements FirewallRepositoryInterface
{
    protected $model;
    protected $cacheTtl = 1800; // 30 minutes

    public function __construct(Firewall $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return Cache::remember('firewalls.all', $this->cacheTtl, function () {
            return $this->model->with('site')
                ->orderBy('name')
                ->get();
        });
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with(['site', 'haPeer'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Firewall
    {
        return Cache::remember("firewall.{$id}", $this->cacheTtl, function () use ($id) {
            return $this->model->with([
                'site',
                'haPeer',
                'configurationHistories' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(5);
                }
            ])->find($id);
        });
    }

    public function findOrFail(int $id): Firewall
    {
        return $this->model->with(['site', 'haPeer', 'configurationHistories'])->findOrFail($id);
    }

    public function create(array $data): Firewall
    {
        $firewall = $this->model->create($data);
        $this->clearFirewallCache($firewall->id, $firewall->site_id);
        return $firewall;
    }

    public function update(int $id, array $data): bool
    {
        $firewall = $this->findOrFail($id);
        $result = $firewall->update($data);
        
        $this->clearFirewallCache($id, $firewall->site_id);
        return $result;
    }

    public function delete(int $id): bool
    {
        $firewall = $this->findOrFail($id);
        $siteId = $firewall->site_id;
        $result = $firewall->delete();
        
        $this->clearFirewallCache($id, $siteId);
        return $result;
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = $this->model->with(['site', 'haPeer']);

        if (!empty($query)) {
            $search->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('ip_nms', 'like', "%{$query}%")
                  ->orWhere('ip_service', 'like', "%{$query}%")
                  ->orWhere('serial_number', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('model', 'like', "%{$query}%");
            });
        }

        // Appliquer les filtres
        if (!empty($filters['site_id'])) {
            $search->where('site_id', $filters['site_id']);
        }

        if (!empty($filters['firewall_type'])) {
            $search->where('firewall_type', $filters['firewall_type']);
        }

        if (!empty($filters['brand'])) {
            $search->where('brand', $filters['brand']);
        }

        if (!empty($filters['status'])) {
            $search->where('status', $filters['status']);
        }

        if (!empty($filters['high_availability'])) {
            $search->where('high_availability', $filters['high_availability']);
        }

        if (!empty($filters['monitoring_enabled'])) {
            $search->where('monitoring_enabled', $filters['monitoring_enabled']);
        }

        return $search->orderBy('name')->paginate(20);
    }

    public function getBySite(int $siteId): Collection
    {
        return Cache::remember("firewalls.site.{$siteId}", $this->cacheTtl, function () use ($siteId) {
            return $this->model->where('site_id', $siteId)
                ->with('haPeer')
                ->orderBy('name')
                ->get();
        });
    }

    public function getByType(string $type): Collection
    {
        return Cache::remember("firewalls.type.{$type}", $this->cacheTtl, function () use ($type) {
            return $this->model->where('firewall_type', $type)
                ->with('site')
                ->orderBy('name')
                ->get();
        });
    }

    public function getInHaPairs(): Collection
    {
        return Cache::remember('firewalls.ha_pairs', $this->cacheTtl, function () {
            return $this->model->where('high_availability', true)
                ->with(['site', 'haPeer'])
                ->orderBy('name')
                ->get();
        });
    }

    public function getStatistics(): array
    {
        return Cache::remember('firewalls.statistics', $this->cacheTtl, function () {
            $total = $this->model->count();
            $active = $this->model->where('status', true)->count();
            $inactive = $this->model->where('status', false)->count();
            
            // Statistiques par type
            $byType = $this->model->select('firewall_type', DB::raw('count(*) as count'))
                ->groupBy('firewall_type')
                ->orderBy('count', 'desc')
                ->get();

            // Statistiques par marque
            $byBrand = $this->model->select('brand', DB::raw('count(*) as count'))
                ->groupBy('brand')
                ->orderBy('count', 'desc')
                ->get();

            // Firewalls en haute disponibilité
            $inHa = $this->model->where('high_availability', true)->count();

            // Firewalls nécessitant backup
            $needingBackup = $this->model->where(function ($query) {
                $query->whereNull('last_backup')
                      ->orWhere('last_backup', '<', Carbon::now()->subDays(7));
            })->count();

            // Firewalls avec monitoring activé
            $withMonitoring = $this->model->where('monitoring_enabled', true)->count();

            return [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'by_type' => $byType,
                'by_brand' => $byBrand,
                'in_ha' => $inHa,
                'with_monitoring' => $withMonitoring,
                'needing_backup' => $needingBackup,
                'backup_coverage' => $total > 0 ? round((($total - $needingBackup) / $total) * 100, 1) : 0,
                'monitoring_coverage' => $total > 0 ? round(($withMonitoring / $total) * 100, 1) : 0
            ];
        });
    }

    public function getLicenseStatusReport(): array
    {
        return Cache::remember('firewalls.license_report', $this->cacheTtl, function () {
            $firewalls = $this->model->with('site')->get();
            
            $report = [
                'total' => 0,
                'expired' => 0,
                'expiring_soon' => 0,
                'valid' => 0,
                'no_license' => 0,
                'critical' => [],
                'warning' => [],
                'details' => []
            ];

            foreach ($firewalls as $firewall) {
                $report['total']++;
                
                // Vérifier les licences
                $licenses = json_decode($firewall->licenses, true) ?? [];
                
                if (empty($licenses)) {
                    $report['no_license']++;
                    $status = 'no_license';
                    $message = 'Aucune licence configurée';
                } else {
                    $expiredCount = 0;
                    $expiringCount = 0;
                    $validCount = 0;
                    
                    foreach ($licenses as $license) {
                        if (isset($license['expiration_date'])) {
                            $expiration = Carbon::parse($license['expiration_date']);
                            $daysUntilExpiration = Carbon::now()->diffInDays($expiration, false);
                            
                            if ($daysUntilExpiration < 0) {
                                $expiredCount++;
                            } elseif ($daysUntilExpiration <= 30) {
                                $expiringCount++;
                            } else {
                                $validCount++;
                            }
                        }
                    }
                    
                    if ($expiredCount > 0) {
                        $status = 'expired';
                        $message = "{$expiredCount} licence(s) expirée(s)";
                        $report['expired']++;
                        
                        if ($expiredCount >= count($licenses)) {
                            $report['critical'][] = $firewall->name;
                        }
                    } elseif ($expiringCount > 0) {
                        $status = 'expiring_soon';
                        $message = "{$expiringCount} licence(s) expirent bientôt";
                        $report['expiring_soon']++;
                        $report['warning'][] = $firewall->name;
                    } else {
                        $status = 'valid';
                        $message = 'Toutes les licences sont valides';
                        $report['valid']++;
                    }
                }

                $report['details'][] = [
                    'firewall' => $firewall->name,
                    'site' => $firewall->site->name ?? 'N/A',
                    'type' => $firewall->firewall_type,
                    'status' => $status,
                    'message' => $message,
                    'licenses_count' => count($licenses)
                ];
            }

            return $report;
        });
    }

    public function getSecurityPoliciesBySite(): array
    {
        return Cache::remember('firewalls.security_policies_by_site', $this->cacheTtl, function () {
            $firewalls = $this->model->with('site')->get();
            $policiesBySite = [];

            foreach ($firewalls as $firewall) {
                $siteName = $firewall->site->name ?? 'Sans site';
                
                if (!isset($policiesBySite[$siteName])) {
                    $policiesBySite[$siteName] = [
                        'total_policies' => 0,
                        'firewalls_count' => 0,
                        'firewalls' => []
                    ];
                }

                $policiesBySite[$siteName]['firewalls_count']++;
                $policiesBySite[$siteName]['firewalls'][] = $firewall->name;
                
                if ($firewall->security_policies) {
                    $policies = json_decode($firewall->security_policies, true) ?? [];
                    $policiesBySite[$siteName]['total_policies'] += count($policies);
                }
            }

            // Trier par nombre de politiques décroissant
            uasort($policiesBySite, function ($a, $b) {
                return $b['total_policies'] <=> $a['total_policies'];
            });

            return $policiesBySite;
        });
    }

    public function analyzeNatRules(): array
    {
        return Cache::remember('firewalls.nat_analysis', $this->cacheTtl, function () {
            $firewalls = $this->model->get();
            $analysis = [
                'total_rules' => 0,
                'total_firewalls' => 0,
                'firewalls_without_nat' => [],
                'by_type' => [],
                'common_ports' => [],
                'details' => []
            ];

            foreach ($firewalls as $firewall) {
                if (!$firewall->nat_rules) {
                    $analysis['firewalls_without_nat'][] = [
                        'name' => $firewall->name,
                        'site' => $firewall->site->name ?? 'N/A'
                    ];
                    continue;
                }

                $analysis['total_firewalls']++;
                $natRules = json_decode($firewall->nat_rules, true) ?? [];
                $analysis['total_rules'] += count($natRules);

                foreach ($natRules as $rule) {
                    $type = $rule['type'] ?? 'unknown';
                    
                    if (!isset($analysis['by_type'][$type])) {
                        $analysis['by_type'][$type] = 0;
                    }
                    $analysis['by_type'][$type]++;

                    // Analyser les ports communs
                    if (isset($rule['service'])) {
                        $service = strtolower($rule['service']);
                        if (!isset($analysis['common_ports'][$service])) {
                            $analysis['common_ports'][$service] = 0;
                        }
                        $analysis['common_ports'][$service]++;
                    }
                }

                $analysis['details'][] = [
                    'firewall' => $firewall->name,
                    'site' => $firewall->site->name ?? 'N/A',
                    'rules_count' => count($natRules),
                    'types' => $this->countNatTypes($natRules)
                ];
            }

            arsort($analysis['by_type']);
            arsort($analysis['common_ports']);

            return $analysis;
        });
    }

    /**
     * Compter les types de règles NAT
     */
    private function countNatTypes(array $rules): array
    {
        $types = [];
        foreach ($rules as $rule) {
            $type = $rule['type'] ?? 'unknown';
            if (!isset($types[$type])) {
                $types[$type] = 0;
            }
            $types[$type]++;
        }
        return $types;
    }

    /**
     * Récupérer les firewalls avec VPN configuré
     */
    public function getFirewallsWithVpn(): Collection
    {
        return Cache::remember('firewalls.with_vpn', $this->cacheTtl, function () {
            return $this->model->whereNotNull('vpn_configuration')
                ->with('site')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Analyser les configurations VPN
     */
    public function analyzeVpnConfigurations(): array
    {
        $firewalls = $this->model->whereNotNull('vpn_configuration')->get();
        
        $analysis = [
            'total_vpn_configurations' => 0,
            'by_type' => [],
            'common_protocols' => [],
            'details' => []
        ];

        foreach ($firewalls as $firewall) {
            $vpnConfig = json_decode($firewall->vpn_configuration, true) ?? [];
            
            if (empty($vpnConfig)) {
                continue;
            }

            $analysis['total_vpn_configurations']++;

            foreach ($vpnConfig as $config) {
                $type = $config['type'] ?? 'unknown';
                $protocol = $config['protocol'] ?? 'unknown';
                
                if (!isset($analysis['by_type'][$type])) {
                    $analysis['by_type'][$type] = 0;
                }
                $analysis['by_type'][$type]++;

                if (!isset($analysis['common_protocols'][$protocol])) {
                    $analysis['common_protocols'][$protocol] = 0;
                }
                $analysis['common_protocols'][$protocol]++;
            }

            $analysis['details'][] = [
                'firewall' => $firewall->name,
                'site' => $firewall->site->name ?? 'N/A',
                'vpn_count' => count($vpnConfig),
                'types' => $this->countVpnTypes($vpnConfig)
            ];
        }

        arsort($analysis['by_type']);
        arsort($analysis['common_protocols']);

        return $analysis;
    }

    /**
     * Compter les types de VPN
     */
    private function countVpnTypes(array $configs): array
    {
        $types = [];
        foreach ($configs as $config) {
            $type = $config['type'] ?? 'unknown';
            if (!isset($types[$type])) {
                $types[$type] = 0;
            }
            $types[$type]++;
        }
        return $types;
    }

    /**
     * Vider le cache des firewalls
     */
    private function clearFirewallCache(?int $firewallId = null, ?int $siteId = null): void
    {
        Cache::forget('firewalls.all');
        Cache::forget('firewalls.statistics');
        Cache::forget('firewalls.ha_pairs');
        Cache::forget('firewalls.with_vpn');
        Cache::forget('firewalls.license_report');
        Cache::forget('firewalls.security_policies_by_site');
        Cache::forget('firewalls.nat_analysis');
        
        if ($firewallId) {
            Cache::forget("firewall.{$firewallId}");
        }
        
        if ($siteId) {
            Cache::forget("firewalls.site.{$siteId}");
            Cache::forget("site.{$siteId}");
            Cache::forget("site.{$siteId}.devices");
        }
        
        // Nettoyer les caches par type
        $types = ['palo_alto', 'fortinet', 'checkpoint', 'cisco_asa', 'other'];
        foreach ($types as $type) {
            Cache::forget("firewalls.type.{$type}");
        }
        
        // Nettoyer les caches liés aux backups
        for ($i = 1; $i <= 30; $i++) {
            Cache::forget("firewalls.needing_backup.{$i}");
        }
    }
}