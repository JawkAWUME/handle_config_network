<?php
// app/Repositories/RouterRepository.php

namespace App\Repositories;

use App\Repositories\Contracts\RouterRepositoryInterface;
use App\Models\Router;
use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RouterRepository implements RouterRepositoryInterface
{
    protected $model;
    protected $cacheTtl = 1800; // 30 minutes

    public function __construct(Router $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return Cache::remember('routers.all', $this->cacheTtl, function () {
            return $this->model->with('site')
                ->orderBy('name')
                ->get();
        });
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with('site')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Router
    {
        return Cache::remember("router.{$id}", $this->cacheTtl, function () use ($id) {
            return $this->model->with([
                'site',
                'configurationHistories' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(5);
                }
            ])->find($id);
        });
    }

    public function findOrFail(int $id): Router
    {
        return $this->model->with(['site', 'configurationHistories'])->findOrFail($id);
    }

    public function create(array $data): Router
    {
        $router = $this->model->create($data);
        $this->clearRouterCache($router->id, $router->site_id);
        return $router;
    }

    public function update(int $id, array $data): bool
    {
        $router = $this->findOrFail($id);
        $result = $router->update($data);
        
        $this->clearRouterCache($id, $router->site_id);
        return $result;
    }

    public function delete(int $id): bool
    {
        $router = $this->findOrFail($id);
        $siteId = $router->site_id;
        $result = $router->delete();
        
        $this->clearRouterCache($id, $siteId);
        return $result;
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = $this->model->with('site');

        if (!empty($query)) {
            $search->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('management_ip', 'like', "%{$query}%")
                  ->orWhere('serial_number', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('model', 'like', "%{$query}%");
            });
        }

        // Appliquer les filtres
        if (!empty($filters['site_id'])) {
            $search->where('site_id', $filters['site_id']);
        }

        if (!empty($filters['brand'])) {
            $search->where('brand', $filters['brand']);
        }

        if (!empty($filters['status'])) {
            $search->where('status', $filters['status']);
        }

        if (!empty($filters['needs_backup'])) {
            $search->where(function ($q) {
                $q->whereNull('last_backup')
                  ->orWhere('last_backup', '<', Carbon::now()->subDays(7));
            });
        }

        if (!empty($filters['high_availability'])) {
            $search->where('high_availability', $filters['high_availability']);
        }

        return $search->orderBy('name')->paginate(20);
    }

    public function getBySite(int $siteId): Collection
    {
        return Cache::remember("routers.site.{$siteId}", $this->cacheTtl, function () use ($siteId) {
            return $this->model->where('site_id', $siteId)
                ->orderBy('name')
                ->get();
        });
    }

    public function getByBrand(string $brand): Collection
    {
        return Cache::remember("routers.brand.{$brand}", $this->cacheTtl, function () use ($brand) {
            return $this->model->where('brand', $brand)
                ->with('site')
                ->orderBy('name')
                ->get();
        });
    }

    public function getNeedingBackup(int $days = 7): Collection
    {
        return Cache::remember("routers.needing_backup.{$days}", 300, function () use ($days) {
            return $this->model->where(function ($query) use ($days) {
                    $query->whereNull('last_backup')
                          ->orWhere('last_backup', '<', Carbon::now()->subDays($days));
                })
                ->with('site')
                ->orderBy('last_backup')
                ->get();
        });
    }

    public function getStatistics(): array
    {
        return Cache::remember('routers.statistics', $this->cacheTtl, function () {
            $total = $this->model->count();
            $active = $this->model->where('status', true)->count();
            $inactive = $this->model->where('status', false)->count();
            
            // Statistiques par marque
            $byBrand = $this->model->select('brand', DB::raw('count(*) as count'))
                ->groupBy('brand')
                ->orderBy('count', 'desc')
                ->get();

            // Routeurs en haute disponibilité
            $inHa = $this->model->where('high_availability', true)->count();

            // Routeurs nécessitant backup
            $needingBackup = $this->model->where(function ($query) {
                $query->whereNull('last_backup')
                      ->orWhere('last_backup', '<', Carbon::now()->subDays(7));
            })->count();

            // Protocoles de routage utilisés
            $routingProtocols = [];
            $routers = $this->model->whereNotNull('routing_protocols')->get();
            
            foreach ($routers as $router) {
                $protocols = json_decode($router->routing_protocols, true) ?? [];
                foreach ($protocols as $protocol) {
                    if (!isset($routingProtocols[$protocol])) {
                        $routingProtocols[$protocol] = 0;
                    }
                    $routingProtocols[$protocol]++;
                }
            }

            arsort($routingProtocols);

            return [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'by_brand' => $byBrand,
                'in_ha' => $inHa,
                'needing_backup' => $needingBackup,
                'backup_coverage' => $total > 0 ? round((($total - $needingBackup) / $total) * 100, 1) : 0,
                'routing_protocols' => $routingProtocols
            ];
        });
    }

    public function createBackup(int $routerId, int $userId, ?string $notes = null): bool
    {
        $router = $this->findOrFail($routerId);
        
        if (empty($router->configuration)) {
            throw new \Exception('Le routeur n\'a pas de configuration à sauvegarder');
        }

        \App\Models\ConfigurationHistory::create([
            'device_type' => Router::class,
            'device_id' => $routerId,
            'configuration' => $router->configuration,
            'user_id' => $userId,
            'change_type' => 'manual_backup',
            'notes' => $notes ?? 'Backup manuel',
            'ip_address' => request()->ip()
        ]);

        $router->update(['last_backup' => Carbon::now()]);
        
        $this->clearRouterCache($routerId, $router->site_id);
        Cache::forget('routers.statistics');
        Cache::forget("routers.needing_backup.7");

        return true;
    }

    public function getBackupHistory(int $routerId): Collection
    {
        return \App\Models\ConfigurationHistory::where('device_type', Router::class)
            ->where('device_id', $routerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLargeConfigurations(int $sizeThreshold = 100000): Collection
    {
        return $this->model->whereRaw('LENGTH(configuration) > ?', [$sizeThreshold])
            ->with('site')
            ->orderByRaw('LENGTH(configuration) DESC')
            ->get();
    }

    public function getByRoutingProtocol(string $protocol): Collection
    {
        return Cache::remember("routers.protocol.{$protocol}", $this->cacheTtl, function () use ($protocol) {
            return $this->model->where('routing_protocols', 'like', "%{$protocol}%")
                ->with('site')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Récupérer les routeurs en haute disponibilité
     */
    public function getHaRouters(): Collection
    {
        return Cache::remember('routers.ha', $this->cacheTtl, function () {
            return $this->model->where('high_availability', true)
                ->with(['site', 'haPeer'])
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Analyser l'utilisation des interfaces
     */
    public function getInterfaceUtilization(): array
    {
        $routers = $this->model->whereNotNull('interfaces')->get();
        
        $totalInterfaces = 0;
        $upInterfaces = 0;
        $downInterfaces = 0;
        $utilizationByRouter = [];

        foreach ($routers as $router) {
            $interfaces = json_decode($router->interfaces, true) ?? [];
            
            if (empty($interfaces)) {
                continue;
            }

            $routerUp = 0;
            $routerDown = 0;
            $routerTotal = count($interfaces);

            foreach ($interfaces as $interface) {
                if (($interface['status'] ?? 'down') === 'up') {
                    $routerUp++;
                } else {
                    $routerDown++;
                }
            }

            $totalInterfaces += $routerTotal;
            $upInterfaces += $routerUp;
            $downInterfaces += $routerDown;

            $utilizationByRouter[] = [
                'router' => $router->name,
                'total_interfaces' => $routerTotal,
                'up_interfaces' => $routerUp,
                'down_interfaces' => $routerDown,
                'uptime_percentage' => $routerTotal > 0 ? round(($routerUp / $routerTotal) * 100, 1) : 0
            ];
        }

        return [
            'total_interfaces' => $totalInterfaces,
            'up_interfaces' => $upInterfaces,
            'down_interfaces' => $downInterfaces,
            'overall_uptime' => $totalInterfaces > 0 ? round(($upInterfaces / $totalInterfaces) * 100, 1) : 0,
            'by_router' => $utilizationByRouter
        ];
    }

    /**
     * Vérifier l'état de la mémoire
     */
    public function getMemoryStatus(): array
    {
        $routers = $this->model->whereNotNull('memory_total')
            ->whereNotNull('memory_used')
            ->get();
        
        $status = [
            'critical' => 0,
            'warning' => 0,
            'normal' => 0,
            'details' => []
        ];

        foreach ($routers as $router) {
            $utilization = ($router->memory_used / $router->memory_total) * 100;
            
            if ($utilization >= 90) {
                $level = 'critical';
                $status['critical']++;
            } elseif ($utilization >= 75) {
                $level = 'warning';
                $status['warning']++;
            } else {
                $level = 'normal';
                $status['normal']++;
            }

            $status['details'][] = [
                'router' => $router->name,
                'memory_total' => $router->memory_total,
                'memory_used' => $router->memory_used,
                'memory_free' => $router->memory_total - $router->memory_used,
                'utilization_percentage' => round($utilization, 1),
                'status' => $level
            ];
        }

        return $status;
    }

    /**
     * Vider le cache des routeurs
     */
    private function clearRouterCache(?int $routerId = null, ?int $siteId = null): void
    {
        Cache::forget('routers.all');
        Cache::forget('routers.statistics');
        Cache::forget('routers.ha');
        
        if ($routerId) {
            Cache::forget("router.{$routerId}");
        }
        
        if ($siteId) {
            Cache::forget("routers.site.{$siteId}");
            Cache::forget("site.{$siteId}");
            Cache::forget("site.{$siteId}.devices");
        }
        
        // Nettoyer les caches par marque et protocole
        $brands = ['Cisco', 'Juniper', 'Mikrotik', 'HP', 'Other'];
        foreach ($brands as $brand) {
            Cache::forget("routers.brand.{$brand}");
        }
        
        $protocols = ['ospf', 'bgp', 'rip', 'eigrp', 'static'];
        foreach ($protocols as $protocol) {
            Cache::forget("routers.protocol.{$protocol}");
        }
        
        // Nettoyer les caches liés aux backups
        for ($i = 1; $i <= 30; $i++) {
            Cache::forget("routers.needing_backup.{$i}");
        }
    }
}