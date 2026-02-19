<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;

// Modèles
use App\Models\Site;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use App\Models\AccessLog;
use App\Models\ConfigurationHistory;
use App\Models\User;

// Policies
use App\Policies\SitePolicy;
use App\Policies\SwitchPolicy;
use App\Policies\RouterPolicy;
use App\Policies\FirewallPolicy;
use App\Policies\AccessLogPolicy;
use App\Policies\ConfigurationHistoryPolicy;
use App\Policies\UserPolicy;

class AppServiceProvider extends AuthServiceProvider
{
    /**
     * ✅ Enregistrement explicite des policies.
     *
     * POURQUOI c'est nécessaire ici :
     * Laravel détecte automatiquement les policies UNIQUEMENT si le nom
     * du modèle correspond exactement au nom de la policy.
     * Ex : User → UserPolicy ✅ (auto-détecté)
     *      SwitchModel → SwitchModelPolicy ✅ (auto-détecté)
     *      SwitchModel → SwitchPolicy      ❌ (PAS auto-détecté → false)
     *
     * C'est pourquoi Gate::allows('viewAny', SwitchModel::class) retournait
     * toujours false : la SwitchPolicy n'était jamais trouvée.
     */
    protected $policies = [
        Site::class                 => SitePolicy::class,
        SwitchModel::class          => SwitchPolicy::class,        // ✅ nom != SwitchModelPolicy
        Router::class               => RouterPolicy::class,
        Firewall::class             => FirewallPolicy::class,
        AccessLog::class            => AccessLogPolicy::class,
        ConfigurationHistory::class => ConfigurationHistoryPolicy::class,
        User::class                 => UserPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ OBLIGATOIRE : enregistre les policies définies dans $policies
        $this->registerPolicies();

        // ── Gate global super-admin (optionnel mais recommandé) ──────
        // Permet à un super-admin de tout bypasser sans toucher aux policies.
        // Retourne null pour les autres rôles → les policies s'appliquent.
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole('super-admin')) {
                return true;
            }
            return null; // laisser les policies décider
        });
    }
}