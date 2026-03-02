<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SiteController extends Controller
{
    /**
     * Liste des sites (avec compteurs d'équipements).
     */
    public function getSites(Request $request)
    {
        try {
            $query = Site::query()
                ->withCount(['switches', 'routers', 'firewalls']);

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
                });
            }

            $sites = $query->orderBy('name')->get()->map(fn($s) => $this->formatSite($s));

            return response()->json(['success' => true, 'data' => $sites]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Détail d'un site.
     */
    public function getSite($id)
    {
        try {
            $site = Site::withCount(['switches', 'routers', 'firewalls'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $this->formatSite($site)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Création d'un site.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Site::class);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'nullable|string|max:50|unique:sites,code',
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:255',
            'country'           => 'nullable|string|max:255',
            'postal_code'       => 'nullable|string|max:20',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'technical_contact' => 'nullable|string|max:255',
            'technical_email'   => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:50',
            'description'       => 'nullable|string',
            'notes'             => 'nullable|string',
            // Associations équipements (tableaux d'IDs)
            'switches_ids'      => 'nullable|array',
            'switches_ids.*'    => 'integer|exists:switches,id',
            'routers_ids'       => 'nullable|array',
            'routers_ids.*'     => 'integer|exists:routers,id',
            'firewalls_ids'     => 'nullable|array',
            'firewalls_ids.*'   => 'integer|exists:firewalls,id',
        ]);

        try {
            // Créer le site sans les IDs d'équipements
            $siteData = collect($validated)
                ->except(['switches_ids', 'routers_ids', 'firewalls_ids'])
                ->toArray();

            $site = Site::create($siteData);

            // Associer les équipements (belongsTo → mettre à jour site_id)
            $this->syncEquipment(
                $site->id,
                $validated['switches_ids']  ?? [],
                $validated['routers_ids']   ?? [],
                $validated['firewalls_ids'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Site créé avec succès',
                'data'    => $this->formatSite($site->fresh()->loadCount(['switches', 'routers', 'firewalls'])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mise à jour d'un site.
     * ─────────────────────────────────────────────────────────────────
     * CORRECTION du HTTP 500 : le controller reçoit switches_ids /
     * routers_ids / firewalls_ids depuis le modal mais ne les traitait
     * pas → exception Laravel "Undefined property" → 500.
     * On les valide puis on appelle syncEquipment().
     * ─────────────────────────────────────────────────────────────────
     */
    public function update(Request $request, $id)
    {
        $site = Site::findOrFail($id);
        Gate::authorize('update', $site);

        $validated = $request->validate([
            'name'              => 'sometimes|required|string|max:255',
            'code'              => 'nullable|string|max:50|unique:sites,code,' . $id,
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:255',
            'country'           => 'nullable|string|max:255',
            'postal_code'       => 'nullable|string|max:20',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'technical_contact' => 'nullable|string|max:255',
            'technical_email'   => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:50',
            'description'       => 'nullable|string',
            'notes'             => 'nullable|string',
            // Associations équipements
            'switches_ids'      => 'nullable|array',
            'switches_ids.*'    => 'integer|exists:switches,id',
            'routers_ids'       => 'nullable|array',
            'routers_ids.*'     => 'integer|exists:routers,id',
            'firewalls_ids'     => 'nullable|array',
            'firewalls_ids.*'   => 'integer|exists:firewalls,id',
        ]);

        try {
            // Mettre à jour les champs scalaires uniquement
            $siteData = collect($validated)
                ->except(['switches_ids', 'routers_ids', 'firewalls_ids'])
                ->toArray();

            $site->update($siteData);

            // Synchroniser les équipements si les tableaux sont présents
            // (présence optionnelle : si le modal n'envoie pas les IDs on ne touche pas aux associations)
            if ($request->has('switches_ids') || $request->has('routers_ids') || $request->has('firewalls_ids')) {
                $this->syncEquipment(
                    $site->id,
                    $validated['switches_ids']  ?? [],
                    $validated['routers_ids']   ?? [],
                    $validated['firewalls_ids'] ?? []
                );
            }

            $fresh = $site->fresh()->loadCount(['switches', 'routers', 'firewalls']);

            return response()->json([
                'success' => true,
                'message' => 'Site mis à jour',
                'data'    => $this->formatSite($fresh),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Suppression d'un site.
     */
    public function destroy($id)
    {
        $site = Site::findOrFail($id);
        Gate::authorize('delete', $site);

        try {
            // Dissocier les équipements avant suppression
            SwitchModel::where('site_id', $id)->update(['site_id' => null]);
            Router::where('site_id', $id)->update(['site_id' => null]);
            Firewall::where('site_id', $id)->update(['site_id' => null]);

            $site->delete();

            return response()->json(['success' => true, 'message' => 'Site supprimé']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export des sites.
     */
    public function export()
    {
        Gate::authorize('viewAny', Site::class);

        $sites = Site::withCount(['switches', 'routers', 'firewalls'])->get();

        return response()->json([
            'success' => true,
            'data'    => $sites->map(fn($s) => $this->formatSite($s)),
            'total'   => $sites->count(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS PRIVÉS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Synchronise les équipements d'un site via site_id (belongsTo).
     * Prérequis : site_id nullable en BDD (migration fournie).
     */
    private function syncEquipment(int $siteId, array $switchIds, array $routerIds, array $firewallIds): void
    {
        // ── Switches ──────────────────────────────────────────────────
        // Dissocier ceux qui étaient sur ce site mais retirés de la liste
        // (site_id doit être nullable en BDD — cf. migration fournie)
        SwitchModel::where('site_id', $siteId)
            ->when(!empty($switchIds), fn($q) => $q->whereNotIn('id', $switchIds))
            ->update(['site_id' => null]);

        if (!empty($switchIds)) {
            SwitchModel::whereIn('id', $switchIds)->update(['site_id' => $siteId]);
        }

        // ── Routers ───────────────────────────────────────────────────
        Router::where('site_id', $siteId)
            ->when(!empty($routerIds), fn($q) => $q->whereNotIn('id', $routerIds))
            ->update(['site_id' => null]);

        if (!empty($routerIds)) {
            Router::whereIn('id', $routerIds)->update(['site_id' => $siteId]);
        }

        // ── Firewalls ─────────────────────────────────────────────────
        Firewall::where('site_id', $siteId)
            ->when(!empty($firewallIds), fn($q) => $q->whereNotIn('id', $firewallIds))
            ->update(['site_id' => null]);

        if (!empty($firewallIds)) {
            Firewall::whereIn('id', $firewallIds)->update(['site_id' => $siteId]);
        }
    }

    /**
     * Formate un site pour la réponse JSON (inclut les IDs des équipements).
     */
    private function formatSite(Site $site): array
    {
        return [
            'id'              => $site->id,
            'name'            => $site->name,
            'code'            => $site->code,
            'address'         => $site->address,
            'city'            => $site->city,
            'country'         => $site->country,
            'postal_code'     => $site->postal_code,
            'latitude'        => $site->latitude,
            'longitude'       => $site->longitude,
            'technical_contact' => $site->technical_contact,
            'technical_email' => $site->technical_email,
            'phone'           => $site->phone,
            'description'     => $site->description,
            'notes'           => $site->notes,
            'switches_count'  => $site->switches_count ?? 0,
            'routers_count'   => $site->routers_count  ?? 0,
            'firewalls_count' => $site->firewalls_count ?? 0,
            // IDs des équipements associés (utiles côté JS pour pré-sélection en édition)
            'switches_ids'    => SwitchModel::where('site_id', $site->id)->pluck('id')->toArray(),
            'routers_ids'     => Router::where('site_id', $site->id)->pluck('id')->toArray(),
            'firewalls_ids'   => Firewall::where('site_id', $site->id)->pluck('id')->toArray(),
            'created_at'      => $site->created_at,
            'updated_at'      => $site->updated_at,
        ];
    }
}