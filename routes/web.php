<?php
// routes/web.php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FirewallController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\SwitchController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegisterForm']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Routes du dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/sites', [DashboardController::class, 'sites'])->name('dashboard.sites');
Route::get('/switches', [DashboardController::class, 'switches'])->name('dashboard.switches');
Route::get('/routers', [DashboardController::class, 'routers'])->name('dashboard.routers');
Route::get('/firewalls', [DashboardController::class, 'firewalls'])->name('dashboard.firewalls');

// Routes API pour les firewalls (JSON uniquement)
Route::prefix('api/firewalls')->name('api.firewalls.')->group(function () {
    Route::get('/statistics', [FirewallController::class, 'getStatistics'])->name('statistics');
    Route::get('/list', [FirewallController::class, 'getFirewalls'])->name('list');
    Route::get('/dashboard-kpis', [FirewallController::class, 'getDashboardKpis'])->name('dashboard-kpis');
    Route::get('/{id}', [FirewallController::class, 'getFirewall'])->name('show');
    Route::post('/', [FirewallController::class, 'store'])->name('store');
    Route::put('/{id}', [FirewallController::class, 'update'])->name('update');
    Route::delete('/{id}', [FirewallController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/test-connectivity', [FirewallController::class, 'testConnectivity'])->name('test-connectivity');
    Route::post('/{id}/update-security-policies', [FirewallController::class, 'updateSecurityPolicies'])->name('update-security-policies');
});

// Routes API pour les routeurs (JSON uniquement)
Route::prefix('api/routers')->name('api.routers.')->group(function () {
    Route::get('/statistics', [RouterController::class, 'getStatistics'])->name('statistics');
    Route::get('/list', [RouterController::class, 'getRouters'])->name('list');
    Route::get('/dashboard-kpis', [RouterController::class, 'getDashboardKpis'])->name('dashboard-kpis');
    Route::get('/{id}', [RouterController::class, 'getRouter'])->name('show');
    Route::post('/', [RouterController::class, 'store'])->name('store');
    Route::put('/{id}', [RouterController::class, 'update'])->name('update');
    Route::delete('/{id}', [RouterController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/test-connectivity', [RouterController::class, 'testConnectivity'])->name('test-connectivity');
    Route::post('/{id}/update-interfaces', [RouterController::class, 'updateInterfaces'])->name('update-interfaces');
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Routes pour les switches
    Route::prefix('switches')->group(function () {
        Route::get('/statistics', [SwitchController::class, 'getStatistics']);
        Route::get('/dashboard-kpis', [SwitchController::class, 'getDashboardKpis']);
        Route::get('/', [SwitchController::class, 'getSwitches']);
        Route::post('/', [SwitchController::class, 'store']);
        Route::get('/export', [SwitchController::class, 'export']);
        
        Route::prefix('{switch}')->group(function () {
            Route::get('/', [SwitchController::class, 'getSwitch']);
            Route::put('/', [SwitchController::class, 'update']);
            Route::patch('/', [SwitchController::class, 'update']);
            Route::delete('/', [SwitchController::class, 'destroy']);
            Route::get('/test-connectivity', [SwitchController::class, 'testConnectivity']);
            Route::post('/port-configuration', [SwitchController::class, 'updatePortConfiguration']);
        });
    });
});

// Routes API pour les exports (garder le format actuel)
Route::get('/firewalls/export', [FirewallController::class, 'export'])->name('firewalls.export');
Route::get('/routers/export', [RouterController::class, 'export'])->name('routers.export');