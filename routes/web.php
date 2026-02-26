<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FirewallController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\SwitchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Routes publiques (guest uniquement)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

});


/*
|--------------------------------------------------------------------------
| Routes protégées (auth obligatoire)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sites', [DashboardController::class, 'sites'])->name('dashboard.sites');
    Route::get('/switches', [DashboardController::class, 'switches'])->name('dashboard.switches');
    Route::get('/routers', [DashboardController::class, 'routers'])->name('dashboard.routers');
    Route::get('/firewalls', [DashboardController::class, 'firewalls'])->name('dashboard.firewalls');


    /*
    |--------------------------------------------------------------------------
    | API Firewalls
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/firewalls')->name('api.firewalls.')->group(function () {

        Route::get('/statistics', [FirewallController::class, 'getStatistics']);
        Route::get('/list', [FirewallController::class, 'getFirewalls']);
        Route::get('/dashboard-kpis', [FirewallController::class, 'getDashboardKpis']);
        Route::get('/{id}', [FirewallController::class, 'getFirewall']);
        Route::post('/', [FirewallController::class, 'store']);
        Route::put('/{id}', [FirewallController::class, 'update']);
        Route::delete('/{id}', [FirewallController::class, 'destroy']);
        Route::post('/{id}/test-connectivity', [FirewallController::class, 'testConnectivity']);
        Route::post('/{id}/update-security-policies', [FirewallController::class, 'updateSecurityPolicies']);

    });


    /*
    |--------------------------------------------------------------------------
    | API Routeurs
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/routers')->name('api.routers.')->group(function () {

        Route::get('/statistics', [RouterController::class, 'getStatistics']);
        Route::get('/list', [RouterController::class, 'getRouters']);
        Route::get('/dashboard-kpis', [RouterController::class, 'getDashboardKpis']);
        Route::get('/{id}', [RouterController::class, 'getRouter']);
        Route::post('/', [RouterController::class, 'store']);
        Route::put('/{id}', [RouterController::class, 'update']);
        Route::delete('/{id}', [RouterController::class, 'destroy']);
        Route::post('/{id}/test-connectivity', [RouterController::class, 'testConnectivity']);
        Route::post('/{id}/update-interfaces', [RouterController::class, 'updateInterfaces']);

    });


    /*
    |--------------------------------------------------------------------------
    | API Switches
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/switches')->name('api.switches.')->group(function () {

        Route::get('/statistics', [SwitchController::class, 'getStatistics']);
        Route::get('/dashboard-kpis', [SwitchController::class, 'getDashboardKpis']);
        Route::get('/', [SwitchController::class, 'getSwitches']);
        Route::post('/', [SwitchController::class, 'store']);
        Route::get('/export', [SwitchController::class, 'export'])->name('export');

        Route::prefix('{switch}')->group(function () {

            Route::get('/', [SwitchController::class, 'getSwitch']);
            Route::put('/', [SwitchController::class, 'update']);
            Route::patch('/', [SwitchController::class, 'update']);
            Route::delete('/', [SwitchController::class, 'destroy']);
            Route::get('/test-connectivity', [SwitchController::class, 'testConnectivity']);
            Route::post('/port-configuration', [SwitchController::class, 'updatePortConfiguration']);

        });

    });
    Route::prefix('api/sites')->name('api.sites.')->group(function () {
        Route::get('/list',    [SiteController::class, 'getSites']);
        Route::get('/{id}',   [SiteController::class, 'getSite']);
        Route::post('/',      [SiteController::class, 'store']);
        Route::put('/{id}',   [SiteController::class, 'update']);
        Route::delete('/{id}',[SiteController::class, 'destroy']);
    });

    Route::get('/sites/export', [SiteController::class, 'export'])->name('sites.export');


    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    */
    Route::get('/firewalls/export', [FirewallController::class, 'export'])->name('firewalls.export');
    Route::get('/routers/export', [RouterController::class, 'export'])->name('routers.export');

});
