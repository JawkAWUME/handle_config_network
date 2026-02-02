<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Site;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher le tableau de bord principal
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les données de base nécessaires pour le dashboard
        $baseData = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'sites_count' => Site::count(),
            'switches_count' => SwitchModel::count(),
            'routers_count' => Router::count(),
            'firewalls_count' => Firewall::count(),
            'recent_updates' => $this->getRecentUpdates(),
        ];
        
        return view('dashboard.index', $baseData);
    }

    /**
     * Afficher la vue pour la gestion des sites
     */
    public function sites()
    {
        return view('dashboard.sites');
    }

    /**
     * Afficher la vue pour la gestion des switchs
     */
    public function switches()
    {
        return view('dashboard.switches');
    }

    /**
     * Afficher la vue pour la gestion des routeurs
     */
    public function routers()
    {
        return view('dashboard.routers');
    }

    /**
     * Afficher la vue pour la gestion des firewalls
     */
    public function firewalls()
    {
        return view('dashboard.firewalls');
    }

    /**
     * Récupérer les mises à jour récentes
     */
    private function getRecentUpdates()
    {
        $recentUpdates = [];
        
        // Récupérer les 5 derniers switchs modifiés
        $recentSwitches = SwitchModel::with('site')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($switch) {
                return [
                    'type' => 'switch',
                    'id' => $switch->id,
                    'name' => $switch->name,
                    'site' => $switch->site->name ?? 'N/A',
                    'updated_at' => $switch->updated_at->format('Y-m-d H:i:s'),
                    'updated_at_human' => $switch->updated_at->diffForHumans(),
                ];
            });
        
        // Récupérer les 5 derniers routeurs modifiés
        $recentRouters = Router::with('site')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($router) {
                return [
                    'type' => 'router',
                    'id' => $router->id,
                    'name' => $router->name,
                    'site' => $router->site->name ?? 'N/A',
                    'updated_at' => $router->updated_at->format('Y-m-d H:i:s'),
                    'updated_at_human' => $router->updated_at->diffForHumans(),
                ];
            });
        
        // Récupérer les 5 derniers firewalls modifiés
        $recentFirewalls = Firewall::with('site')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($firewall) {
                return [
                    'type' => 'firewall',
                    'id' => $firewall->id,
                    'name' => $firewall->name,
                    'site' => $firewall->site->name ?? 'N/A',
                    'updated_at' => $firewall->updated_at->format('Y-m-d H:i:s'),
                    'updated_at_human' => $firewall->updated_at->diffForHumans(),
                ];
            });
        
        // Fusionner et trier par date
        $recentUpdates = collect($recentSwitches)
            ->merge($recentRouters)
            ->merge($recentFirewalls)
            ->sortByDesc('updated_at')
            ->values()
            ->take(5);
        
        return $recentUpdates;
    }
}