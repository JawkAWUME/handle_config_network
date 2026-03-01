<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiteExport;

class SiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Lister tous les sites (JSON)
     */
    public function getSites(Request $request)
    {
        Gate::authorize('viewAny', Site::class);

        try {
            $search = $request->input('search');
            $limit  = $request->input('limit', 10);

            $query = Site::query()->withCount(['switches', 'routers', 'firewalls']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $sites = $query->orderBy('name')->limit($limit)->get();

            return response()->json([
                'success'   => true,
                'data'      => $sites,
                'total'     => $sites->count(),
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des sites : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer un site spécifique (JSON)
     */
    public function getSite($id)
    {
        try {
            $site = Site::with(['switches', 'routers', 'firewalls'])->findOrFail($id);

            Gate::authorize('view', $site);

            return response()->json([
                'success'   => true,
                'data'      => $site,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du site : ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Créer un site (JSON)
     *
     * Les champs envoyés par le modal utilisent les noms réels de la BDD :
     *   technical_contact, technical_email, phone, code, status, description...
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Site::class);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'nullable|string|max:50|unique:sites,code',
            'address'            => 'nullable|string|max:500',
            'city'               => 'nullable|string|max:255',
            'country'            => 'nullable|string|max:255',
            'postal_code'        => 'nullable|string|max:20',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            // Champs contact (noms BDD réels)
            'contact_name'  => 'nullable|string|max:255',   // ← anciennement contact_name
            'contact_email' => 'nullable|email|max:255',    // ← anciennement contact_email
            'contact_phone'      => 'nullable|string|max:50',    // ← anciennement contact_phone
            'description'        => 'nullable|string',
            'status'             => 'nullable|string|max:50',
            'notes'              => 'nullable|string',
        ]);

        try {
            $site = Site::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Site créé avec succès',
                'data'    => $this->formatSite($site),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mettre à jour un site (JSON)
     */
    public function update(Request $request, $id)
    {
        $site = Site::findOrFail($id);
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'name'               => 'sometimes|required|string|max:255',
            'code'               => 'sometimes|nullable|string|max:50|unique:sites,code,' . $id,
            'address'            => 'sometimes|nullable|string|max:500',
            'city'               => 'sometimes|nullable|string|max:255',
            'country'            => 'sometimes|nullable|string|max:255',
            'postal_code'        => 'sometimes|nullable|string|max:20',
            'latitude'           => 'sometimes|nullable|numeric',
            'longitude'          => 'sometimes|nullable|numeric',
            'technical_contact'  => 'sometimes|nullable|string|max:255',
            'technical_email'    => 'sometimes|nullable|email|max:255',
            'phone'              => 'sometimes|nullable|string|max:50',
            'description'        => 'sometimes|nullable|string',
            'status'             => 'sometimes|nullable|string|max:50',
            'notes'              => 'sometimes|nullable|string',
        ]);

        try {
            $site->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Site mis à jour avec succès',
                'data'    => $this->formatSite($site->fresh()),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un site (JSON)
     */
    public function destroy($id)
    {
        $site = Site::findOrFail($id);
        Gate::authorize('delete', $site);

        try {
            $site->delete();

            return response()->json([
                'success' => true,
                'message' => 'Site supprimé avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporter les sites en Excel
     */
    public function export(Request $request)
    {
        Gate::authorize('export', Site::class);

        try {
            return Excel::download(
                new SiteExport(),
                'sites-export-' . date('Y-m-d-His') . '.xlsx'
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formater un site pour Alpine.js (même structure que sitesForJs dans DashboardController)
     * Garantit que la réponse JSON après create/update est directement utilisable par le frontend.
     */
    private function formatSite(Site $site): array
    {
        return [
            'id'                => $site->id,
            'name'              => $site->name,
            'code'              => $site->code,
            'address'           => $site->address,
            'postal_code'       => $site->postal_code,
            'city'              => $site->city,
            'country'           => $site->country,
            'technical_contact' => $site->technical_contact,
            'technical_email'   => $site->technical_email,
            'phone'             => $site->phone,
            'description'       => $site->description,
            'status'            => $site->status,
            'switches_count'    => $site->switches()->count(),
            'routers_count'     => $site->routers()->count(),
            'firewalls_count'   => $site->firewalls()->count(),
        ];
    }
}