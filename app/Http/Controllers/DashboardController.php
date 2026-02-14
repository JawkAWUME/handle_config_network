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
        
        // Sites – si l'utilisateur peut voir la liste
        $sitesCount = Gate::allows('viewAny', Site::class) 
            ? Site::count() 
            : $user->sites()->count(); // relation éventuelle (sites assignés)

        // Switches – avec politique viewAny
        $switchesCount = Gate::allows('viewAny', SwitchModel::class) 
            ? SwitchModel::count() 
            : 0; // ou filtre personnalisé

        $routersCount = Gate::allows('viewAny', Router::class) 
            ? Router::count() 
            : 0;

        $firewallsCount = Gate::allows('viewAny', Firewall::class) 
            ? Firewall::count() 
            : 0;

        // Derniers équipements ajoutés (limité aux permissions)
        $recentSwitches = $this->getRecentModels(SwitchModel::class);
        $recentRouters   = $this->getRecentModels(Router::class);
        $recentFirewalls = $this->getRecentModels(Firewall::class);

        // Derniers backups
       // Derniers backups visibles
        $recentBackups = Backup::recent(5)
            ->visibleForUser($user)
            ->with('backupable')
            ->get();

        // Alertes actives visibles
        $activeAlerts = Alert::open()
            ->recent(5)
            ->visibleForUser($user)
            ->with('alertable')
            ->get();

        // État des équipements (en ligne / hors ligne)
        $onlineStats = [
            'switches' => SwitchModel::where('status', 'online')->count(),
            'routers'  => Router::where('status', 'online')->count(),
            'firewalls'=> Firewall::where('status', 'online')->count(),
        ];

        // ------------------------------------------------------------
        // 2. Permissions pour les actions du dashboard (affichage conditionnel)
        // ------------------------------------------------------------
        $can = [
            'createSite'       => Gate::allows('create', Site::class),
            'createSwitch'     => Gate::allows('create', SwitchModel::class),
            'createRouter'     => Gate::allows('create', Router::class),
            'deleteRouter'     => Gate::allows('delete', Router::class),
            'createFirewall'   => Gate::allows('create', Firewall::class),
            'viewAnySite'      => Gate::allows('viewAny', Site::class),
            'viewAnySwitch'    => Gate::allows('viewAny', SwitchModel::class),
            'viewAnyRouter'    => Gate::allows('viewAny', Router::class),
            'viewAnyFirewall'  => Gate::allows('viewAny', Firewall::class),
            'viewAnyBackup'    => Gate::allows('viewAny', Backup::class), // si policy existe
            'admin'            => $user->hasRole('admin'),
        ];

        return view('dashboard.index', compact(
            'sitesCount',
            'switchesCount',
            'routersCount',
            'firewallsCount',
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

        private function getRecentModels(string $modelClass)
    {
        if (!Gate::allows('viewAny', $modelClass)) {
            return collect();
        }
        return $modelClass::latest()->limit(3)->get();
    }
}