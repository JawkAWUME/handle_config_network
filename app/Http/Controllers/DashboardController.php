<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\Site;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use App\Models\Alert;
use App\Models\Backup;

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

        if (!$user) {
            return redirect()->route('login');
        }

        // ------------------------------------------------------------
        // 1. Récupération des données avec filtrage automatique via Policies
        // ------------------------------------------------------------
        
        // Récupérer les collections complètes (avec permissions)
        $sites = Gate::allows('viewAny', Site::class) 
            ? Site::with(['switches', 'routers', 'firewalls'])->get()
            : collect();

        $switches = Gate::allows('viewAny', SwitchModel::class) 
            ? SwitchModel::with('site', 'user')->get()
            : collect();

        $routers = Gate::allows('viewAny', Router::class) 
            ? Router::with('site', 'user')->get()
            : collect();

        $firewalls = Gate::allows('viewAny', Firewall::class) 
            ? Firewall::with('site', 'user')->get()
            : collect();

        // Compter les équipements
        $sitesCount = $sites->count();
        $switchesCount = $switches->count();
        $routersCount = $routers->count();
        $firewallsCount = $firewalls->count();

        // ------------------------------------------------------------
        // 2. Calculer les totaux pour le dashboard
        // ------------------------------------------------------------
        $totals = [
            'sites' => $sitesCount,
            'firewalls' => $firewallsCount,
            'routers' => $routersCount,
            'switches' => $switchesCount,
            'devices' => $firewallsCount + $routersCount + $switchesCount,
            'availability' => 99.7, // À calculer selon votre logique
            'avgUptime' => 45, // À calculer selon votre logique
            'incidentsToday' => Alert::whereDate('created_at', today())->count(),
        ];

        // ------------------------------------------------------------
        // 3. Préparer les données pour les graphiques Chart.js
        // ------------------------------------------------------------
        $chartData = [
            'deviceDistribution' => [
                'labels' => ['Firewalls', 'Routeurs', 'Switchs'],
                'data' => [$firewallsCount, $routersCount, $switchesCount],
                'colors' => ['#ef4444', '#10b981', '#0ea5e9']
            ],
            'availabilityData' => [
                'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                'data' => $this->getWeeklyAvailability()
            ],
            // 'incidentsData' => [
            //     'labels' => ['Connexion', 'CPU', 'Mémoire', 'Bande Passante', 'Disque'],
            //     'data' => $this->getIncidentsByType()
            // ],
            'loadData' => [
                'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                'firewalls' => $this->getEquipmentLoad('firewall'),
                'routers' => $this->getEquipmentLoad('router'),
                'switches' => $this->getEquipmentLoad('switch')
            ]
        ];

        // ------------------------------------------------------------
        // 4. Derniers équipements ajoutés
        // ------------------------------------------------------------
        $recentSwitches = $this->getRecentModels(SwitchModel::class);
        $recentRouters = $this->getRecentModels(Router::class);
        $recentFirewalls = $this->getRecentModels(Firewall::class);

        // ------------------------------------------------------------
        // 5. Derniers backups et alertes
        // ------------------------------------------------------------
        $recentBackups = Backup::latest()
            ->limit(5)
            ->get();

        $activeAlerts = Alert::where('status', 'open')
            ->latest()
            ->limit(5)
            ->get();

        // ------------------------------------------------------------
        // 6. État des équipements (en ligne / hors ligne)
        // ------------------------------------------------------------
        $onlineStats = [
            'switches' => $switches->where('status', 'active')->count(),
            'routers' => $routers->where('status', true)->count(),
            'firewalls' => $firewalls->where('status', true)->count(),
        ];

        // ------------------------------------------------------------
        // 7. Permissions pour les actions du dashboard
        // ------------------------------------------------------------
        $can = [
            'create' => Gate::allows('create', Site::class),
            'export' => Gate::allows('viewAny', Site::class),
            'viewAnySite' => Gate::allows('viewAny', Site::class),
            'viewAnySwitch' => Gate::allows('viewAny', SwitchModel::class),
            'viewAnyRouter' => Gate::allows('viewAny', Router::class),
            'viewAnyFirewall' => Gate::allows('viewAny', Firewall::class),
        ];

        return view('dashboard.index', compact(
            'sites',
            'switches',
            'routers',
            'firewalls',
            'totals',
            'chartData',
            'recentSwitches',
            'recentRouters',
            'recentFirewalls',
            'recentBackups',
            'activeAlerts',
            'onlineStats',
            'can'
        ));
    }

    /**
     * Obtenir la disponibilité hebdomadaire
     */
    private function getWeeklyAvailability()
    {
        // TODO: Implémenter la logique réelle
        // Pour l'instant, retourner des données simulées
        return [99.2, 99.5, 99.8, 99.7, 99.6, 99.9, 99.4];
    }

    /**
     * Obtenir les incidents par type
     */
    private function getIncidentsByType()
    {
        // TODO: Implémenter la logique réelle basée sur votre modèle Alert
        // Pour l'instant, retourner des données simulées
        $incidents = [
            'connection' => Alert::where('type', 'connection')->whereDate('created_at', today())->count(),
            'cpu' => Alert::where('type', 'cpu')->whereDate('created_at', today())->count(),
            'memory' => Alert::where('type', 'memory')->whereDate('created_at', today())->count(),
            'bandwidth' => Alert::where('type', 'bandwidth')->whereDate('created_at', today())->count(),
            'disk' => Alert::where('type', 'disk')->whereDate('created_at', today())->count(),
        ];
        
        return array_values($incidents);
    }

    /**
     * Obtenir la charge des équipements
     */
    private function getEquipmentLoad(string $type)
    {
        // TODO: Implémenter la logique réelle
        // Pour l'instant, retourner des données simulées
        $loads = [
            'firewall' => [45, 48, 62, 68, 55, 50],
            'router' => [60, 58, 72, 78, 65, 62],
            'switch' => [40, 42, 55, 58, 48, 45]
        ];
        
        return $loads[$type] ?? [0, 0, 0, 0, 0, 0];
    }

    /**
     * Récupérer les modèles récents
     */
    private function getRecentModels(string $modelClass)
    {
        if (!Gate::allows('viewAny', $modelClass)) {
            return collect();
        }
        return $modelClass::with('site')->latest()->limit(3)->get();
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
}