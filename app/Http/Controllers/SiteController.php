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
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Site::class);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city'          => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:20',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'contact_name'  => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'description'   => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        try {
            $site = Site::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Site créé avec succès',
                'data'    => $site,
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
            'name'    => 'sometimes|required|string|max:255',
            'code'    => 'sometimes|required|string|max:50|unique:sites,code,' . $id,
            'address' => 'nullable|string|max:500',
        ]);

        try {
            $site->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Site mis à jour avec succès',
                'data'    => $site->fresh(),
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
}