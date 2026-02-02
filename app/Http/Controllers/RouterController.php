<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Services\RouterService;
use App\Exports\RouterExport;
use App\Http\Requests\Router\StoreRouterRequest;
use App\Http\Requests\Router\UpdateRouterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class RouterController extends Controller
{
    protected $routerService;

    public function __construct(RouterService $routerService)
    {
        $this->middleware('auth');
        $this->routerService = $routerService;
    }

    /**
     * Récupérer les statistiques des routeurs (JSON)
     */
    public function getStatistics(Request $request)
    {
        Gate::authorize('viewAny', Router::class);

        try {
            $statistics = $this->routerService->getRouterStatistics();
            
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
     * Récupérer la liste des routeurs (JSON)
     */
    public function getRouters(Request $request)
    {
        Gate::authorize('viewAny', Router::class);

        try {
            $search = $request->input('search');
            $status = $request->input('status');
            $vendor = $request->input('vendor');
            $site_id = $request->input('site_id');
            $limit = $request->input('limit', 10);

            $query = Router::query()
                ->with(['site:id,name'])
                ->withCount(['interfaces', 'interfaces as interfaces_up_count' => function ($q) {
                    $q->where('status', 'up');
                }]);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('management_ip', 'like', "%{$search}%")
                      ->orWhereHas('site', function ($siteQuery) use ($search) {
                          $siteQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status === 'active');
            }

            if ($vendor && $vendor !== 'all') {
                $query->where('brand', $vendor);
            }

            if ($site_id && $site_id !== 'all') {
                $query->where('site_id', $site_id);
            }

            $routers = $query->orderBy('name')->limit($limit)->get();
            
            $formattedRouters = $routers->map(function ($router) {
                return [
                    'id' => $router->id,
                    'name' => $router->name,
                    'model' => $router->model,
                    'site' => $router->site ? [
                        'id' => $router->site->id,
                        'name' => $router->site->name
                    ] : null,
                    'brand' => $router->brand,
                    'management_ip' => $router->management_ip,
                    'status' => $router->status,
                    'interfaces_count' => $router->interfaces_count,
                    'interfaces_up_count' => $router->interfaces_up_count,
                    'created_at' => $router->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $router->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedRouters,
                'total' => $routers->count(),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des routeurs : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un routeur spécifique (JSON)
     */
    public function getRouter($id)
    {
        try {
            $router = Router::with([
                'site:id,name,address',
                'configurationHistories' => function ($q) {
                    $q->latest()->limit(5);
                }
            ])->findOrFail($id);

            Gate::authorize('view', $router);

            // Enrichir avec les stats du service
            $enrichedRouter = $this->routerService->getRouter($id);
            
            return response()->json([
                'success' => true,
                'data' => $enrichedRouter,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du routeur : ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Tester la connectivité d'un routeur (JSON)
     */
    public function testConnectivity($id)
    {
        $router = Router::findOrFail($id);
        Gate::authorize('update', $router);

        try {
            $results = $this->routerService->testConnectivity($id);
            
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
     * Récupérer les KPI pour le dashboard
     */
    public function getDashboardKpis()
    {
        Gate::authorize('viewAny', Router::class);

        try {
            $statistics = $this->routerService->getRouterStatistics();
            
            // Calculer les KPI spécifiques pour le dashboard
            $kpis = [
                'total' => $statistics['total'] ?? 0,
                'active' => $statistics['by_status']['active'] ?? 0,
                'inactive' => $statistics['by_status']['inactive'] ?? 0,
                'needing_backup' => $statistics['needing_backup'] ?? 0,
                'average_interfaces' => $statistics['average_interfaces_per_router'] ?? 0,
                'by_brand' => $statistics['by_brand'] ?? [],
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
     * Mettre à jour les interfaces d'un routeur (JSON)
     */
    public function updateInterfaces(Request $request, $id)
    {
        $router = Router::findOrFail($id);
        Gate::authorize('update', $router);

        $request->validate([
            'interfaces' => 'required|array',
            'interfaces.*.name' => 'required|string',
            'interfaces.*.ip_address' => 'nullable|ipv4',
            'interfaces.*.status' => 'required|in:up,down,admin',
        ]);

        try {
            $this->routerService->updateInterfaces($id, $request->input('interfaces'));
            
            return response()->json([
                'success' => true,
                'message' => 'Interfaces mises à jour avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    // Les autres méthodes (store, update, destroy, export, etc.) restent inchangées
    // mais retournent également du JSON au lieu de redirections
    
    /**
     * Créer un routeur (JSON)
     */
    public function store(StoreRouterRequest $request)
    {
        try {
            $router = $this->routerService->createRouter($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Routeur créé avec succès',
                'data' => $router,
                'redirect' => route('routers.show', $router->id)
            ], 201);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mettre à jour un routeur (JSON)
     */
    public function update(UpdateRouterRequest $request, $id)
    {
        try {
            $router = $this->routerService->updateRouter($id, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Routeur mis à jour avec succès',
                'data' => $router
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Supprimer un routeur (JSON)
     */
    public function destroy($id)
    {
        $router = Router::findOrFail($id);
        Gate::authorize('delete', $router);

        try {
            $this->routerService->deleteRouter($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Routeur supprimé avec succès'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
}