<?php

namespace App\Http\Controllers;

use App\Models\Firewall;
use App\Services\FirewallService;
use App\Exports\FirewallExport;
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
                ->with(['site:id,name']);

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
            
            $formattedFirewalls = $firewalls->map(fn($firewall) => $this->formatFirewall($firewall));

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
    public function store(Request $request)
    {
        Gate::authorize('create', Firewall::class);

        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'site_id'                  => 'nullable|integer|exists:sites,id',
            'brand'                    => 'nullable|string|max:100',
            'model'                    => 'nullable|string|max:100',
            'firewall_type'            => 'nullable|string|in:palo_alto,fortinet,cisco_asa,checkpoint,other',
            'ip_nms'                   => 'nullable|string|max:45',
            'ip_service'               => 'nullable|string|max:45',
            'vlan_nms'                 => 'nullable|integer|min:1|max:4094',
            'vlan_service'             => 'nullable|integer|min:1|max:4094',
            'username'                 => 'nullable|string|max:100',
            'password'                 => 'nullable|string|max:255',
            'enable_password'          => 'nullable|string|max:255',
            'firmware_version'         => 'nullable|string|max:50',
            'serial_number'            => 'nullable|string|max:100',
            'asset_tag'                => 'nullable|string|max:100',
            'security_policies_count'  => 'nullable|integer|min:0',
            'cpu'                      => 'nullable|integer|min:0|max:100',
            'memory'                   => 'nullable|integer|min:0|max:100',
            'high_availability'        => 'nullable|boolean',
            'monitoring_enabled'       => 'nullable|boolean',
            'status'                   => 'nullable|string|in:active,warning,danger',
            'configuration'            => 'nullable|string',
            'notes'                    => 'nullable|string',
        ]);

        // Convertir statut string → booléen pour la BDD
        if (isset($validated['status'])) {
            $validated['status'] = $validated['status'] === 'active';
        }

        try {
            $firewall = Firewall::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Firewall créé avec succès',
                'data'    => $this->formatFirewall($firewall->load('site')),
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
    public function update(Request $request, $id)
    {
        $firewall = Firewall::findOrFail($id);
        Gate::authorize('update', $firewall);

        $validated = $request->validate([
            'name'                     => 'sometimes|required|string|max:255',
            'site_id'                  => 'nullable|integer|exists:sites,id',
            'brand'                    => 'nullable|string|max:100',
            'model'                    => 'nullable|string|max:100',
            'firewall_type'            => 'nullable|string|in:palo_alto,fortinet,cisco_asa,checkpoint,other',
            'ip_nms'                   => 'nullable|string|max:45',
            'ip_service'               => 'nullable|string|max:45',
            'vlan_nms'                 => 'nullable|integer|min:1|max:4094',
            'vlan_service'             => 'nullable|integer|min:1|max:4094',
            'username'                 => 'nullable|string|max:100',
            'password'                 => 'nullable|string|max:255',
            'enable_password'          => 'nullable|string|max:255',
            'firmware_version'         => 'nullable|string|max:50',
            'serial_number'            => 'nullable|string|max:100',
            'asset_tag'                => 'nullable|string|max:100',
            'security_policies_count'  => 'nullable|integer|min:0',
            'cpu'                      => 'nullable|integer|min:0|max:100',
            'memory'                   => 'nullable|integer|min:0|max:100',
            'high_availability'        => 'nullable|boolean',
            'monitoring_enabled'       => 'nullable|boolean',
            'status'                   => 'nullable|string|in:active,warning,danger',
            'configuration'            => 'nullable|string',
            'notes'                    => 'nullable|string',
        ]);

        if (isset($validated['status'])) {
            $validated['status'] = $validated['status'] === 'active';
        }

        try {
            $firewall->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Firewall mis à jour avec succès',
                'data'    => $this->formatFirewall($firewall->fresh()->load('site')),
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

    /**
     * Formater un firewall pour la réponse JSON (compatible frontend Alpine)
     */
    private function formatFirewall(Firewall $fw): array
    {
        $toStatus = fn($v) => ($v === true || $v == 1) ? 'active' : 'danger';
        return [
            'id'                       => $fw->id,
            'name'                     => $fw->name,
            'brand'                    => $fw->brand,
            'model'                    => $fw->model,
            'firewall_type'            => $fw->firewall_type,
            'status'                   => $toStatus($fw->status),
            'username'                 => $fw->username,
            'ip_nms'                   => $fw->ip_nms,
            'ip_service'               => $fw->ip_service,
            'vlan_nms'                 => $fw->vlan_nms,
            'vlan_service'             => $fw->vlan_service,
            'firmware_version'         => $fw->firmware_version,
            'security_policies_count'  => $fw->security_policies_count ?? (is_array($fw->security_policies) ? count($fw->security_policies) : 0),
            'cpu'                      => $fw->cpu ?? 0,
            'memory'                   => $fw->memory ?? 0,
            'high_availability'        => (bool) $fw->high_availability,
            'monitoring_enabled'       => (bool) $fw->monitoring_enabled,
            'serial_number'            => $fw->serial_number,
            'asset_tag'                => $fw->asset_tag,
            'notes'                    => $fw->notes,
            'updated_at'               => $fw->updated_at?->toISOString(),
            'site'                     => $fw->site?->name ?? 'N/A',
            'site_id'                  => $fw->site_id,
        ];
    }
}