<?php

namespace App\Http\Controllers;

use App\Models\SwitchModel;
use App\Services\SwitchService;
use App\Exports\SwitchExport;
use App\Http\Requests\Switch\StoreSwitchRequest;
use App\Http\Requests\Switch\UpdateSwitchRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class SwitchController extends Controller
{
    protected $switchService;

    public function __construct(SwitchService $switchService)
    {
        $this->middleware('auth');
        $this->switchService = $switchService;
    }

    /**
     * Récupérer les statistiques des switches (JSON)
     */
    public function getStatistics(Request $request)
    {
        Gate::authorize('viewAny', SwitchModel::class);

        try {
            $statistics = $this->switchService->getSwitchStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer la liste des switches (JSON)
     */
    public function getSwitches(Request $request)
    {
        Gate::authorize('viewAny', SwitchModel::class);

        try {
            $search = $request->input('search');
            $status = $request->input('status');
            $brand = $request->input('brand');
            $site_id = $request->input('site_id');
            $switch_type = $request->input('switch_type');
            $limit = $request->input('limit', 10);
            $port_utilization = $request->input('port_utilization');

            $query = SwitchModel::query()
                ->with(['site:id,name,code'])
                ->withCount(['ports']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('ip_nms', 'like', "%{$search}%")
                      ->orWhere('ip_service', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%")
                      ->orWhereHas('site', function ($siteQuery) use ($search) {
                          $siteQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            if ($brand && $brand !== 'all') {
                $query->where('brand', $brand);
            }

            if ($site_id && $site_id !== 'all') {
                $query->where('site_id', $site_id);
            }

            if ($switch_type && $switch_type !== 'all') {
                $query->where('model', 'like', "%{$switch_type}%");
            }

            if ($port_utilization && $port_utilization !== 'all') {
                if ($port_utilization === 'high') {
                    $query->whereRaw('(ports_used / ports_total) >= 0.8');
                } elseif ($port_utilization === 'medium') {
                    $query->whereRaw('(ports_used / ports_total) BETWEEN 0.5 AND 0.79');
                } elseif ($port_utilization === 'low') {
                    $query->whereRaw('(ports_used / ports_total) < 0.5');
                }
            }

            $switches = $query->orderBy('site_id')
                ->orderBy('name')
                ->limit($limit)
                ->get();
            
            $formattedSwitches = $switches->map(function ($switch) {
                return [
                    'id' => $switch->id,
                    'name' => $switch->name,
                    'model' => $switch->model,
                    'brand' => $switch->brand,
                    'site' => $switch->site ? [
                        'id' => $switch->site->id,
                        'name' => $switch->site->name,
                        'code' => $switch->site->code
                    ] : null,
                    'ip_nms' => $switch->ip_nms,
                    'ip_service' => $switch->ip_service,
                    'ports_total' => $switch->ports_total,
                    'ports_used' => $switch->ports_used,
                    'port_utilization' => $switch->ports_total > 0 ? 
                        round(($switch->ports_used / $switch->ports_total) * 100, 2) : 0,
                    'status' => $switch->status,
                    'last_backup' => $switch->last_backup?->format('Y-m-d H:i:s'),
                    'ports_count' => $switch->ports_count,
                    'created_at' => $switch->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $switch->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedSwitches,
                'total' => $switches->count(),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des switches : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un switch spécifique (JSON)
     */
    public function getSwitch($id)
    {
        try {
            $switch = SwitchModel::with([
                'site:id,name,code,address',
                'configurationHistories' => function ($q) {
                    $q->latest()->limit(5);
                },
                'ports' => function ($q) {
                    $q->orderBy('number');
                }
            ])->findOrFail($id);

            Gate::authorize('view', $switch);

            // Enrichir avec les stats du service
            $enrichedSwitch = $this->switchService->getSwitch($id);
            
            return response()->json([
                'success' => true,
                'data' => $enrichedSwitch,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du switch : ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Tester la connectivité d'un switch (JSON)
     */
    public function testConnectivity($id)
    {
        $switch = SwitchModel::findOrFail($id);
        Gate::authorize('update', $switch);

        try {
            $results = $this->switchService->testConnectivity($id);
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Test de connectivité terminé'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour la configuration des ports (JSON)
     */
    public function updatePortConfiguration(Request $request, $id)
    {
        $switch = SwitchModel::findOrFail($id);
        Gate::authorize('update', $switch);

        $request->validate([
            'port_config' => 'required|array',
            'port_config.*.status' => 'required|in:enabled,disabled',
            'port_config.*.vlan' => 'nullable|integer|min:1|max:4094',
            'port_config.*.description' => 'nullable|string|max:100',
        ]);

        try {
            $updatedSwitch = $this->switchService->updatePortConfiguration($id, $request->port_config);
            
            return response()->json([
                'success' => true,
                'data' => $updatedSwitch,
                'message' => 'Configuration des ports mise à jour avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la configuration : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les KPI pour le dashboard
     */
    public function getDashboardKpis()
    {
        Gate::authorize('viewAny', SwitchModel::class);

        try {
            $statistics = $this->switchService->getSwitchStatistics();
            
            // Calculer les KPI spécifiques pour le dashboard
            $kpis = [
                'total' => $statistics['total'] ?? 0,
                'total_ports' => $statistics['total_ports'] ?? 0,
                'used_ports' => $statistics['used_ports'] ?? 0,
                'available_ports' => ($statistics['total_ports'] ?? 0) - ($statistics['used_ports'] ?? 0),
                'average_utilization' => $statistics['average_utilization'] ?? 0,
                'needing_backup' => $statistics['needing_backup'] ?? 0,
                'by_brand' => $statistics['by_brand'] ?? [],
                'by_type' => $statistics['by_type'] ?? [],
                'by_site' => $statistics['by_site'] ?? [],
            ];
            
            return response()->json([
                'success' => true,
                'data' => $kpis,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des KPI : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter les switches en Excel
     */
    public function export(Request $request)
    {
        Gate::authorize('viewAny', SwitchModel::class);

        try {
            $vendor = $request->input('vendor');
            $site = $request->input('site');
            
            return Excel::download(
                new SwitchExport($vendor, $site),
                'switches-export-' . date('Y-m-d-His') . '.xlsx'
            );
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un switch (JSON)
     */
    public function store(StoreSwitchRequest $request)
    {
        Gate::authorize('create', SwitchModel::class);

        try {
            $switch = $this->switchService->createSwitch($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Switch créé avec succès',
                'data' => $switch,
                'redirect' => route('switches.show', $switch->id)
            ], 201);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mettre à jour un switch (JSON)
     */
    public function update(UpdateSwitchRequest $request, $id)
    {
        $switch = SwitchModel::findOrFail($id);
        Gate::authorize('update', $switch);

        try {
            $updatedSwitch = $this->switchService->updateSwitch($id, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Switch mis à jour avec succès',
                'data' => $updatedSwitch
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Supprimer un switch (JSON)
     */
    public function destroy($id)
    {
        $switch = SwitchModel::findOrFail($id);
        Gate::authorize('delete', $switch);

        try {
            $this->switchService->deleteSwitch($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Switch supprimé avec succès'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
}