<?php
// app/Repositories/SiteRepository.php

namespace App\Repositories;

use App\Repositories\Contracts\SiteRepositoryInterface;
use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SiteRepository implements SiteRepositoryInterface
{
    protected $model;
    protected $cacheTtl = 3600; // 1 heure

    public function __construct(Site $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return Cache::remember('sites.all', $this->cacheTtl, function () {
            return $this->model->withCount(['switches', 'routers', 'firewalls'])
                ->orderBy('name')
                ->get();
        });
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->withCount(['switches', 'routers', 'firewalls'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): ?Site
    {
        return Cache::remember("site.{$id}", $this->cacheTtl, function () use ($id) {
            return $this->model->withCount(['switches', 'routers', 'firewalls'])->find($id);
        });
    }

    public function findOrFail(int $id): Site
    {
        return $this->model->withCount(['switches', 'routers', 'firewalls'])->findOrFail($id);
    }

    public function create(array $data): Site
    {
        $site = $this->model->create($data);
        $this->clearSiteCache();
        return $site;
    }

    public function update(int $id, array $data): bool
    {
        $site = $this->findOrFail($id);
        $result = $site->update($data);
        
        $this->clearSiteCache($id);
        return $result;
    }

    public function delete(int $id): bool
    {
        $site = $this->findOrFail($id);
        $result = $site->delete();
        
        $this->clearSiteCache($id);
        return $result;
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = $this->model->withCount(['switches', 'routers', 'firewalls']);

        if (!empty($query)) {
            $search->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('address', 'like', "%{$query}%")
                  ->orWhere('technical_contact', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Appliquer les filtres
        if (!empty($filters['status'])) {
            $search->where('status', $filters['status']);
        }

        if (!empty($filters['has_devices'])) {
            $search->has($filters['has_devices']);
        }

        return $search->orderBy('name')->paginate(20);
    }

    public function withDevices(int $siteId): ?Site
    {
        return Cache::remember("site.{$siteId}.devices", $this->cacheTtl, function () use ($siteId) {
            return $this->model->with([
                'switches' => function ($query) {
                    $query->orderBy('name')->select(['id', 'name', 'site_id', 'status', 'last_backup']);
                },
                'routers' => function ($query) {
                    $query->orderBy('name')->select(['id', 'name', 'site_id', 'status', 'last_backup']);
                },
                'firewalls' => function ($query) {
                    $query->orderBy('name')->select(['id', 'name', 'site_id', 'status', 'last_backup']);
                }
            ])->find($siteId);
        });
    }

    public function getStatistics(): array
    {
        return Cache::remember('sites.statistics', $this->cacheTtl, function () {
            $total = $this->model->count();
            $active = $this->model->where('status', 'active')->count();
            
            // Compter les sites avec chaque type d'équipement
            $withSwitches = $this->model->has('switches')->count();
            $withRouters = $this->model->has('routers')->count();
            $withFirewalls = $this->model->has('firewalls')->count();

            // Sites avec problèmes
            $sitesWithIssues = $this->model->whereHas('switches', function ($query) {
                    $query->where('status', 'offline');
                })
                ->orWhereHas('routers', function ($query) {
                    $query->where('status', 'offline');
                })
                ->orWhereHas('firewalls', function ($query) {
                    $query->where('status', 'offline');
                })->count();

            return [
                'total' => $total,
                'active' => $active,
                'inactive' => $total - $active,
                'with_switches' => $withSwitches,
                'with_routers' => $withRouters,
                'with_firewalls' => $withFirewalls,
                'sites_with_issues' => $sitesWithIssues,
                'device_counts' => $this->getDeviceCountsBySite()->toArray()
            ];
        });
    }

    public function getActiveSites(): Collection
    {
        return Cache::remember('sites.active', $this->cacheTtl, function () {
            return $this->model->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'technical_contact', 'address']);
        });
    }

    public function getDeviceCountsBySite(): Collection
    {
        return Cache::remember('sites.device_counts', $this->cacheTtl, function () {
            return $this->model->select([
                    'id',
                    'name',
                    DB::raw('(SELECT COUNT(*) FROM switches WHERE switches.site_id = sites.id) as switches_count'),
                    DB::raw('(SELECT COUNT(*) FROM routers WHERE routers.site_id = sites.id) as routers_count'),
                    DB::raw('(SELECT COUNT(*) FROM firewalls WHERE firewalls.site_id = sites.id) as firewalls_count'),
                    DB::raw('(SELECT COUNT(*) FROM switches WHERE switches.site_id = sites.id) + 
                             (SELECT COUNT(*) FROM routers WHERE routers.site_id = sites.id) + 
                             (SELECT COUNT(*) FROM firewalls WHERE firewalls.site_id = sites.id) as total_devices')
                ])
                ->orderBy('name')
                ->get();
        });
    }

    public function getSitesNeedingAttention(): Collection
    {
        return $this->model->where(function ($query) {
                $query->whereHas('switches', function ($q) {
                    $q->where('status', 'offline')
                      ->orWhereNull('last_backup');
                })
                ->orWhereHas('routers', function ($q) {
                    $q->where('status', 'offline')
                      ->orWhereNull('last_backup');
                })
                ->orWhereHas('firewalls', function ($q) {
                    $q->where('status', 'offline')
                      ->orWhereNull('last_backup');
                });
            })
            ->withCount([
                'switches as offline_switches' => function ($query) {
                    $query->where('status', 'offline');
                },
                'switches as switches_without_backup' => function ($query) {
                    $query->whereNull('last_backup');
                },
                'routers as offline_routers' => function ($query) {
                    $query->where('status', 'offline');
                },
                'routers as routers_without_backup' => function ($query) {
                    $query->whereNull('last_backup');
                },
                'firewalls as offline_firewalls' => function ($query) {
                    $query->where('status', 'offline');
                },
                'firewalls as firewalls_without_backup' => function ($query) {
                    $query->whereNull('last_backup');
                }
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Récupérer les sites avec le plus d'équipements
     */
    public function getTopSitesByDeviceCount(int $limit = 10): Collection
    {
        return Cache::remember('sites.top_by_devices', $this->cacheTtl, function () use ($limit) {
            return $this->model->select('id', 'name')
                ->withCount(['switches', 'routers', 'firewalls'])
                ->orderByRaw('(switches_count + routers_count + firewalls_count) DESC')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Récupérer la répartition des sites par région
     */
    public function getSitesByRegion(): Collection
    {
        return $this->model->select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('count', 'DESC')
            ->get();
    }

    /**
     * Vider le cache des sites
     */
    private function clearSiteCache(?int $siteId = null): void
    {
        Cache::forget('sites.all');
        Cache::forget('sites.active');
        Cache::forget('sites.statistics');
        Cache::forget('sites.device_counts');
        Cache::forget('sites.top_by_devices');
        
        if ($siteId) {
            Cache::forget("site.{$siteId}");
            Cache::forget("site.{$siteId}.devices");
        }
    }
}