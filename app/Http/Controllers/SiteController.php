<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SwitchModel;   // ← ajout
use App\Models\Router;        // ← ajout
use App\Models\Firewall;
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
     * Les champs de contact utilisés par le modal Alpine.js sont :
     *   technical_contact, technical_email, phone
     * qui correspondent directement aux colonnes de la table `sites`.
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
            'technical_contact'  => 'nullable|string|max:255',
            'technical_email'    => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'description'        => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        try {
            $site = Site::create($validated);
            $site->loadCount(['switches', 'routers', 'firewalls']);

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
            'name'               => 'sometimes|required|string|max:255',
            'code'               => 'sometimes|nullable|string|max:50|unique:sites,code,' . $id,
            'address'            => 'nullable|string|max:500',
            'city'               => 'nullable|string|max:255',
            'country'            => 'nullable|string|max:255',
            'postal_code'        => 'nullable|string|max:20',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'technical_contact'  => 'nullable|string|max:255',
            'technical_email'    => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'description'        => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        try {
            $site->update($validated);

            // Mise à jour des associations équipements
            $switchIds   = $request->input('switches_ids',  []);
            $routerIds   = $request->input('routers_ids',   []);
            $firewallIds = $request->input('firewalls_ids', []);

            // Détacher tous les équipements actuellement liés à ce site
            SwitchModel::where('site_id', $site->id)->update(['site_id' => null]);
            Router::where('site_id', $site->id)->update(['site_id' => null]);
            Firewall::where('site_id', $site->id)->update(['site_id' => null]);

            // Rattacher les équipements sélectionnés
            if (!empty($switchIds))   SwitchModel::whereIn('id', $switchIds)->update(['site_id' => $site->id]);
            if (!empty($routerIds))   Router::whereIn('id', $routerIds)->update(['site_id' => $site->id]);
            if (!empty($firewallIds)) Firewall::whereIn('id', $firewallIds)->update(['site_id' => $site->id]);

            // Recharger avec les compteurs pour ne pas les perdre côté Alpine.js
            $fresh = Site::withCount(['switches', 'routers', 'firewalls'])
                         ->with(['switches:id', 'routers:id', 'firewalls:id'])
                         ->find($site->id);

            return response()->json([
                'success' => true,
                'message' => 'Site mis à jour avec succès',
                'data'    => [
                    'id'                => $fresh->id,
                    'name'              => $fresh->name,
                    'code'              => $fresh->code,
                    'address'           => $fresh->address,
                    'postal_code'       => $fresh->postal_code,
                    'city'              => $fresh->city,
                    'country'           => $fresh->country,
                    'latitude'          => $fresh->latitude,
                    'longitude'         => $fresh->longitude,
                    'technical_contact' => $fresh->technical_contact,
                    'technical_email'   => $fresh->technical_email,
                    'phone'             => $fresh->phone,
                    'description'       => $fresh->description,
                    'notes'             => $fresh->notes,
                    'switches_count'    => $fresh->switches_count,
                    'routers_count'     => $fresh->routers_count,
                    'firewalls_count'   => $fresh->firewalls_count,
                    'switches_ids'      => $fresh->switches->pluck('id'),
                    'routers_ids'       => $fresh->routers->pluck('id'),
                    'firewalls_ids'     => $fresh->firewalls->pluck('id'),
                ],
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