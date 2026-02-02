<?php
// app/Providers/RepositoryServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Tous les bindings de repositories
     */
    protected $repositories = [
        // Interfaces => Implémentations
        \App\Repositories\Contracts\SiteRepositoryInterface::class => 
            \App\Repositories\SiteRepository::class,
        
        \App\Repositories\Contracts\SwitchRepositoryInterface::class => 
            \App\Repositories\SwitchRepository::class,
        
        \App\Repositories\Contracts\RouterRepositoryInterface::class => 
            \App\Repositories\RouterRepository::class,
        
        \App\Repositories\Contracts\FirewallRepositoryInterface::class => 
            \App\Repositories\FirewallRepository::class,
        
        \App\Repositories\Contracts\ConfigurationHistoryRepositoryInterface::class => 
            \App\Repositories\ConfigurationHistoryRepository::class,
        
        \App\Repositories\Contracts\AccessLogRepositoryInterface::class => 
            \App\Repositories\AccessLogRepository::class
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}