<?php
// app/Repositories/SwitchRepository.php

namespace App\Repositories;

use App\Repositories\Contracts\SwitchRepositoryInterface;
use App\Models\SwitchModel;
use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SwitchRepository implements SwitchRepositoryInterface
{
    protected $model;
    protected $cacheTtl = 1800; // 30 minutes

    public function __construct(SwitchModel $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return Cache::remember('switches.all', $this->cacheTtl, function () {
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

    public function find(int $id): ?SwitchModel
    {
        return Cache::remember("switch.{$id}", $this->cacheTtl, function () use ($id) {
            return $this->model->with([
                'site',
                'configurationHistories' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(5);
                }
            ])->find($id);
        });
    }

    public function findOrFail(int $id): SwitchModel
    {
        return $this->model->with(['site', 'configurationHistories'])->findOrFail($id);
    }

    public function create(array $data): SwitchModel
    {
        $switch = $this->model->create($data);
        $this->clearSwitchCache($switch->id, $switch->site_id);
        return $switch;
    }

    public function update(int $id, array $data): bool
    {
        $switch = $this->findOrFail($id);
        $result = $switch->update($data);
        
        $this->clearSwitchCache($id, $switch->site_id);
        return $result;
    }

    public function delete(int $id): bool
    {
        $switch = $this->findOrFail($id);
        $siteId = $switch->site_id;
        $result = $switch->delete();
        
        $this->clearSwitchCache($id, $siteId);
        return $result;
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = $this->model->with('site');

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

        return $search->orderBy('name')->paginate(20);
    }

    public function getBySite(int $siteId): Collection
    {
        return Cache::remember("switches.site.{$siteId}", $this->cacheTtl, function () use ($siteId) {
            return $this->model->where('site_id', $siteId)
                ->orderBy('name')
                ->get();
        });
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with('site')
            ->orderBy('name')
            ->get();
    }

    public function getNeedingBackup(int $days = 7): Collection
    {
        return Cache::remember("switches.needing_backup.{$days}", 300, function () use ($days) {
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
        return Cache::remember('switches.statistics', $this->cacheTtl, function () {
            $total = $this->model->count();
            $online = $this->model->where('status', 'online')->count();
            $offline = $this->model->where('status', 'offline')->count();
            $maintenance = $this->model->where('status', 'maintenance')->count();
            
            // Statistiques par marque
            $byBrand = $this->model->select('brand', DB::raw('count(*) as count'))
                ->groupBy('brand')
                ->orderBy('count', 'desc')
                ->get();

            // Switches nécessitant backup
            $needingBackup = $this->model->where(function ($query) {
                $query->whereNull('last_backup')
                      ->orWhere('last_backup', '<', Carbon::now()->subDays(7));
            })->count();

            // Âge moyen des backups
            $avgBackupAge = $this->model->whereNotNull('last_backup')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(DAY, last_backup, NOW())) as avg_days'))
                ->first()->avg_days ?? 0;

            return [
                'total' => $total,
                'online' => $online,
                'offline' => $offline,
                'maintenance' => $maintenance,
                'by_brand' => $byBrand,
                'needing_backup' => $needingBackup,
                'avg_backup_age' => round($avgBackupAge, 1),
                'backup_coverage' => $total > 0 ? round((($total - $needingBackup) / $total) * 100, 1) : 0
            ];
        });
    }

    public function createBackup(int $switchId, int $userId, ?string $notes = null): bool
    {
        $switch = $this->findOrFail($switchId);
        
        // Vérifier si le switch a une configuration
        if (empty($switch->configuration)) {
            throw new \Exception('Le switch n\'a pas de configuration à sauvegarder');
        }

        // Créer l'entrée d'historique
        \App\Models\ConfigurationHistory::create([
            'device_type' => SwitchModel::class,
            'device_id' => $switchId,
            'configuration' => $switch->configuration,
            'user_id' => $userId,
            'change_type' => 'manual_backup',
            'notes' => $notes ?? 'Backup manuel',
            'ip_address' => request()->ip()
        ]);

        // Mettre à jour la date du dernier backup
        $switch->update(['last_backup' => Carbon::now()]);
        
        $this->clearSwitchCache($switchId, $switch->site_id);
        Cache::forget('switches.statistics');
        Cache::forget("switches.needing_backup.7");

        return true;
    }

    public function getBackupHistory(int $switchId): Collection
    {
        return \App\Models\ConfigurationHistory::where('device_type', SwitchModel::class)
            ->where('device_id', $switchId)
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

    /**
     * Récupérer les switches empilables
     */
    public function getStackableSwitches(): Collection
    {
        return Cache::remember('switches.stackable', $this->cacheTtl, function () {
            return $this->model->where('stackable', true)
                ->with('site')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Récupérer les switches en stack
     */
    public function getSwitchesInStacks(): Collection
    {
        return $this->model->where('in_stack', true)
            ->with(['site', 'configurationHistories' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('stack_role')
            ->get();
    }

    /**
     * Analyser l'utilisation des ports
     */
    public function getPortUtilization(): array
    {
        $switches = $this->model->whereNotNull('ports')->get();
        
        $totalPorts = 0;
        $usedPorts = 0;
        $utilizationBySwitch = [];

        foreach ($switches as $switch) {
            // Simuler une analyse des ports (à adapter selon votre modèle)
            $ports = $switch->ports ?? 24;
            $used = rand(0, $ports); // Simulation
            
            $totalPorts += $ports;
            $usedPorts += $used;
            
            $utilizationBySwitch[] = [
                'switch' => $switch->name,
                'total_ports' => $ports,
                'used_ports' => $used,
                'utilization_percentage' => round(($used / $ports) * 100, 1)
            ];
        }

        return [
            'total_ports' => $totalPorts,
            'used_ports' => $usedPorts,
            'free_ports' => $totalPorts - $usedPorts,
            'overall_utilization' => $totalPorts > 0 ? round(($usedPorts / $totalPorts) * 100, 1) : 0,
            'by_switch' => $utilizationBySwitch
        ];
    }

    /**
     * Vider le cache des switches
     */
    private function clearSwitchCache(?int $switchId = null, ?int $siteId = null): void
    {
        Cache::forget('switches.all');
        Cache::forget('switches.statistics');
        Cache::forget('switches.stackable');
        
        if ($switchId) {
            Cache::forget("switch.{$switchId}");
        }
        
        if ($siteId) {
            Cache::forget("switches.site.{$siteId}");
            Cache::forget("site.{$siteId}");
            Cache::forget("site.{$siteId}.devices");
        }
        
        // Nettoyer les caches liés aux backups
        for ($i = 1; $i <= 30; $i++) {
            Cache::forget("switches.needing_backup.{$i}");
        }
    }
}