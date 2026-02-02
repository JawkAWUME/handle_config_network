<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\FirewallService;
use App\Exports\FirewallExport;
use App\Http\Requests\Firewall\StoreFirewallRequest;
use App\Http\Requests\Firewall\UpdateFirewallRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class FirewallController extends Controller
{
    protected $firewallService;

    public function __construct(FirewallService $firewallService)
    {
        $this->middleware('auth');
        $this->firewallService = $firewallService;
    }

    /**
     * Récupérer les statistiques des firewalls (JSON)
     */
    public function getStatistics(Request $request)
    {
        Gate::authorize('viewAny', Firewall::class);

        try {
            $statistics = $this->firewallService->getFirewallStatistics();
            
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
     * Récupérer la liste des firewalls (JSON)
     */
    public function getFirewalls(Request $request)
    {
        Gate::authorize('viewAny', Firewall::class);

        try {
            $search = $request->input('search');
            $status = $request->input('status');
            $brand = $request->input('brand');
            $site_id = $request->input('site_id');
            $firewall_type = $request->input('firewall_type');
            $limit = $request->input('limit', 10);

            $query = Firewall::query()
                ->with(['site:id,name'])
                ->withCount(['securityPolicies']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('ip_nms', 'like', "%{$search}%")
                      ->orWhere('ip_service', 'like', "%{$search}%")
                      ->orWhereHas('site', function ($siteQuery) use ($search) {
                          $siteQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status === 'active');
            }

            if ($brand && $brand !== 'all') {
                $query->where('brand', $brand);
            }

            if ($site_id && $site_id !== 'all') {
                $query->where('site_id', $site_id);
            }

            if ($firewall_type && $firewall_type !== 'all') {
                $query->where('firewall_type', $firewall_type);
            }

            $firewalls = $query->orderBy('name')->limit($limit)->get();
            
            $formattedFirewalls = $firewalls->map(function ($firewall) {
                return [
                    'id' => $firewall->id,
                    'name' => $firewall->name,
                    'model' => $firewall->model,
                    'site' => $firewall->site ? [
                        'id' => $firewall->site->id,
                        'name' => $firewall->site->name
                    ] : null,
                    'firewall_type' => $firewall->firewall_type,
                    'ip_nms' => $firewall->ip_nms,
                    'ip_service' => $firewall->ip_service,
                    'status' => $firewall->status,
                    'security_policies_count' => $firewall->security_policies_count,
                    'created_at' => $firewall->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $firewall->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedFirewalls,
                'total' => $firewalls->count(),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des firewalls : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un firewall spécifique (JSON)
     */
    public function getFirewall($id)
    {
        try {
            $firewall = Firewall::with([
                'site:id,name,address',
                'configurationHistories' => function ($q) {
                    $q->latest()->limit(5);
                }
            ])->findOrFail($id);

            Gate::authorize('view', $firewall);

            // Enrichir avec les stats du service
            $enrichedFirewall = $this->firewallService->getFirewall($id);
            
            return response()->json([
                'success' => true,
                'data' => $enrichedFirewall,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du firewall : ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Tester la connectivité d'un firewall (JSON)
     */
    public function testConnectivity($id)
    {
        $firewall = Firewall::findOrFail($id);
        Gate::authorize('update', $firewall);

        try {
            $results = $this->firewallService->testConnectivity($id);
            
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
        Gate::authorize('viewAny', Firewall::class);

        try {
            $statistics = $this->firewallService->getFirewallStatistics();
            
            // Calculer les KPI spécifiques pour le dashboard
            $kpis = [
                'total' => $statistics['total'] ?? 0,
                'active' => $statistics['by_status']['active'] ?? 0,
                'inactive' => $statistics['by_status']['inactive'] ?? 0,
                'needing_backup' => $statistics['needing_backup'] ?? 0,
                'average_rules' => $statistics['average_rules_per_firewall'] ?? 0,
                'ha_enabled' => $statistics['ha_enabled'] ?? 0,
                'by_brand' => $statistics['by_brand'] ?? [],
                'by_type' => $statistics['by_type'] ?? [],
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

    // Les autres méthodes (store, update, destroy, export, etc.) restent inchangées
    // mais retournent également du JSON au lieu de redirections
    
    /**
     * Créer un firewall (JSON)
     */
    public function store(StoreFirewallRequest $request)
    {
        try {
            $firewall = $this->firewallService->createFirewall($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Firewall créé avec succès',
                'data' => $firewall,
                'redirect' => route('firewalls.show', $firewall->id)
            ], 201);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mettre à jour un firewall (JSON)
     */
    public function update(UpdateFirewallRequest $request, $id)
    {
        try {
            $firewall = $this->firewallService->updateFirewall($id, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Firewall mis à jour avec succès',
                'data' => $firewall
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Supprimer un firewall (JSON)
     */
    public function destroy($id)
    {
        $firewall = Firewall::findOrFail($id);
        Gate::authorize('delete', $firewall);

        try {
            $this->firewallService->deleteFirewall($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Firewall supprimé avec succès'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
}