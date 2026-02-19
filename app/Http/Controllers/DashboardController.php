<?php

namespace App\Http\Controllers;

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

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // ============================================================
        // 1. Collections Eloquent BRUTES
        //
        // ✅ Gate::allows() fonctionne maintenant car les policies sont
        //    enregistrées dans AppServiceProvider::$policies.
        //    Avant : SwitchModel → SwitchPolicy non détecté → false
        //    Après : mapping explicite → true pour admin/engineer/viewer
        // ============================================================

        $sites = Gate::allows('viewAny', Site::class)
            ? Site::with(['switches', 'routers', 'firewalls'])->get()
            : collect();

        $switchCollection = Gate::allows('viewAny', SwitchModel::class)
            ? SwitchModel::with(['site', 'accessLogs' => fn($q) => $q->latest()->limit(5)->with('user')])->get()
            : collect();

        $routerCollection = Gate::allows('viewAny', Router::class)
            ? Router::with(['site', 'accessLogs' => fn($q) => $q->latest()->limit(5)->with('user')])->get()
            : collect();

        $firewallCollection = Gate::allows('viewAny', Firewall::class)
            ? Firewall::with(['site', 'accessLogs' => fn($q) => $q->latest()->limit(5)->with('user')])->get()
            : collect();

        // ============================================================
        // 2. Comptages sur les collections BRUTES
        //    (avant ->map() qui transforme en tableaux PHP)
        // ============================================================

        $sitesCount     = $sites->count();
        $switchesCount  = $switchCollection->count();
        $routersCount   = $routerCollection->count();
        $firewallsCount = $firewallCollection->count();

        // ============================================================
        // 3. onlineStats — doit se faire sur la collection Eloquent
        //    car ->where() sur un tableau PHP associatif ne fonctionne pas
        // ============================================================

        $onlineStats = [
            'switches'  => $switchCollection->where('status', true)->count(),
            'routers'   => $routerCollection->where('status', true)->count(),
            'firewalls' => $firewallCollection->where('status', true)->count(),
        ];

        // ============================================================
        // 4. Helper : booléen BDD → string Alpine.js
        //
        // La BDD stocke status=1/0, le cast 'boolean' donne true/false.
        // Alpine.js compare avec les strings 'active' | 'warning' | 'danger'.
        // ============================================================

        $toStatus = fn($status) => ($status === true || $status == 1) ? 'active' : 'danger';

        // ============================================================
        // 5. Transformation switches → tableau plat pour Alpine.js
        // ============================================================

        $switches = $switchCollection->map(function ($sw) use ($toStatus) {
            $lastLog    = $sw->accessLogs->first();
            $portsLabel = $sw->ports_total
                ? ($sw->ports_used ?? 0) . '/' . $sw->ports_total . ' ports'
                : 'N/A';

            return [
                'id'               => $sw->id,
                'name'             => $sw->name,
                'brand'            => $sw->brand,
                'model'            => $sw->model,
                'status'           => $toStatus($sw->status),   // string
                'username'         => $sw->username,
                'ip_nms'           => $sw->ip_nms,
                'ip_service'       => $sw->ip_service,
                'vlan_nms'         => $sw->vlan_nms,
                'vlan_service'     => $sw->vlan_service,
                'ports_total'      => $sw->ports_total,
                'ports_used'       => $sw->ports_used,
                'ports'            => $portsLabel,               // "32/48 ports"
                'vlans'            => $sw->vlan_nms ?? 0,
                'serial_number'    => $sw->serial_number,
                'firmware_version' => $sw->firmware_version,
                'updated_at'       => $sw->updated_at?->toISOString(),
                'site'             => $sw->site?->name ?? 'N/A', // STRING
                'site_id'          => $sw->site_id,
                'last_access_user' => $lastLog?->user?->name
                                   ?? $lastLog?->ip_address
                                   ?? 'Aucun accès',
                'last_access_time' => $lastLog?->created_at?->toISOString(),
                'access_logs'      => $sw->accessLogs->map(fn($log) => [
                    'id'         => $log->id,
                    'action'     => $log->action,
                    'result'     => $log->result,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toISOString(),
                    'user'       => $log->user
                        ? ['id' => $log->user->id, 'name' => $log->user->name]
                        : null,
                ])->toArray(),
            ];
        })->values()->toArray();

        // ============================================================
        // 6. Transformation routers
        // ============================================================

        $routers = $routerCollection->map(function ($rt) use ($toStatus) {
            $lastLog = $rt->accessLogs->first();

            return [
                'id'                  => $rt->id,
                'name'                => $rt->name,
                'brand'               => $rt->brand,
                'model'               => $rt->model,
                'status'              => $toStatus($rt->status),
                'username'            => $rt->username,
                'ip_nms'              => $rt->ip_nms,
                'ip_service'          => $rt->ip_service,
                'management_ip'       => $rt->management_ip,
                'vlan_nms'            => $rt->vlan_nms,
                'vlan_service'        => $rt->vlan_service,
                'serial_number'       => $rt->serial_number,
                'updated_at'          => $rt->updated_at?->toISOString(),
                'interfaces_count'    => $rt->interfaces_count    ?? 0,
                'interfaces_up_count' => $rt->interfaces_up_count ?? 0,
                'configuration'       => $rt->configuration,
                'site'                => $rt->site?->name ?? 'N/A',
                'site_id'             => $rt->site_id,
                'last_access_user'    => $lastLog?->user?->name
                                      ?? $lastLog?->ip_address
                                      ?? 'Aucun accès',
                'last_access_time'    => $lastLog?->created_at?->toISOString(),
                'access_logs'         => $rt->accessLogs->map(fn($log) => [
                    'id'         => $log->id,
                    'action'     => $log->action,
                    'result'     => $log->result,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toISOString(),
                    'user'       => $log->user
                        ? ['id' => $log->user->id, 'name' => $log->user->name]
                        : null,
                ])->toArray(),
            ];
        })->values()->toArray();

        // ============================================================
        // 7. Transformation firewalls
        // ============================================================

        $firewalls = $firewallCollection->map(function ($fw) use ($toStatus) {
            $lastLog = $fw->accessLogs->first();

            return [
                'id'                      => $fw->id,
                'name'                    => $fw->name,
                'brand'                   => $fw->brand,
                'model'                   => $fw->model,
                'status'                  => $toStatus($fw->status),
                'username'                => $fw->username,
                'ip_nms'                  => $fw->ip_nms,
                'ip_service'              => $fw->ip_service,
                'vlan_nms'                => $fw->vlan_nms,
                'vlan_service'            => $fw->vlan_service,
                'serial_number'           => $fw->serial_number,
                'enable_password'         => !empty($fw->enable_password),
                'updated_at'              => $fw->updated_at?->toISOString(),
                'security_policies_count' => $fw->security_policies_count ?? 0,
                'cpu'                     => $fw->cpu    ?? 0,
                'memory'                  => $fw->memory ?? 0,
                'configuration'           => $fw->configuration,
                'site'                    => $fw->site?->name ?? 'N/A',
                'site_id'                 => $fw->site_id,
                'last_access_user'        => $lastLog?->user?->name
                                          ?? $lastLog?->ip_address
                                          ?? 'Aucun accès',
                'last_access_time'        => $lastLog?->created_at?->toISOString(),
                'access_logs'             => $fw->accessLogs->map(fn($log) => [
                    'id'         => $log->id,
                    'action'     => $log->action,
                    'result'     => $log->result,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toISOString(),
                    'user'       => $log->user
                        ? ['id' => $log->user->id, 'name' => $log->user->name]
                        : null,
                ])->toArray(),
            ];
        })->values()->toArray();

        // ============================================================
        // 8. Totaux
        // ============================================================

        $totals = [
            'sites'          => $sitesCount,
            'firewalls'      => $firewallsCount,
            'routers'        => $routersCount,
            'switches'       => $switchesCount,
            'devices'        => $firewallsCount + $routersCount + $switchesCount,
            'availability'   => 99.7,
            'avgUptime'      => 45,
            'incidentsToday' => Alert::whereDate('created_at', today())->count(),
        ];

        // ============================================================
        // 9. Chart data
        // ============================================================

        $chartData = [
            'deviceDistribution' => [
                'labels' => ['Firewalls', 'Routeurs', 'Switchs'],
                'data'   => [$firewallsCount, $routersCount, $switchesCount],
                'colors' => ['#ef4444', '#10b981', '#0ea5e9'],
            ],
            'availabilityData' => [
                'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                'data'   => $this->getWeeklyAvailability(),
            ],
            'incidentsData' => [
                'labels' => ['Connexion', 'CPU', 'Mémoire', 'Bande Passante', 'Disque'],
                'data'   => [0, 0, 0, 0, 0],
            ],
            'loadData' => [
                'labels'    => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                'firewalls' => $this->getEquipmentLoad('firewall'),
                'routers'   => $this->getEquipmentLoad('router'),
                'switches'  => $this->getEquipmentLoad('switch'),
            ],
        ];

        // ============================================================
        // 10. Récents / backups / alertes
        // ============================================================

        $recentSwitches  = $this->getRecentModels(SwitchModel::class);
        $recentRouters   = $this->getRecentModels(Router::class);
        $recentFirewalls = $this->getRecentModels(Firewall::class);
        $recentBackups   = Backup::latest()->limit(5)->get();
        $activeAlerts    = Alert::where('status', 'open')->latest()->limit(5)->get();

        // ============================================================
        // 11. Permissions pour la vue Blade (@can / Alpine permissions)
        // ============================================================

        $can = [
            'create'          => Gate::allows('create', Site::class),
            'export'          => Gate::allows('viewAny', Site::class),
            'viewAnySite'     => Gate::allows('viewAny', Site::class),
            'viewAnySwitch'   => Gate::allows('viewAny', SwitchModel::class),
            'viewAnyRouter'   => Gate::allows('viewAny', Router::class),
            'viewAnyFirewall' => Gate::allows('viewAny', Firewall::class),
        ];

        // ============================================================
        // 12. Sites pour Alpine.js — tableau plat [{id, name}]
        //
        // ✅ NE PAS passer la collection Eloquent $sites directement dans
        //    @json() : elle contient des relations imbriquées qui gonflent
        //    le JSON et peuvent provoquer des erreurs de sérialisation.
        // ============================================================

        $sitesForJs = $sites->map(fn($s) => [
            'id'   => $s->id,
            'name' => $s->name,
        ])->values()->toArray();

        return view('dashboard.index', compact(
            'sitesForJs',        // → Alpine : sites: @json($sitesForJs)
            'sites',             // → Blade partials (@foreach)
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

    // ================================================================
    // Méthodes privées
    // ================================================================

    private function getWeeklyAvailability(): array
    {
        return [99.2, 99.5, 99.8, 99.7, 99.6, 99.9, 99.4];
    }

    private function getEquipmentLoad(string $type): array
    {
        return [
            'firewall' => [45, 48, 62, 68, 55, 50],
            'router'   => [60, 58, 72, 78, 65, 62],
            'switch'   => [40, 42, 55, 58, 48, 45],
        ][$type] ?? [0, 0, 0, 0, 0, 0];
    }

    private function getRecentModels(string $modelClass)
    {
        if (!Gate::allows('viewAny', $modelClass)) {
            return collect();
        }
        return $modelClass::with('site')->latest()->limit(3)->get();
    }

    public function sites()     { return view('dashboard.sites'); }
    public function switches()  { return view('dashboard.switches'); }
    public function routers()   { return view('dashboard.routers'); }
    public function firewalls() { return view('dashboard.firewalls'); }
}