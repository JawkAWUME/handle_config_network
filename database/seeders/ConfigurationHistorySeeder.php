<?php
// database/seeders/ConfigurationHistorySeeder.php

namespace Database\Seeders;

use App\Models\ConfigurationHistory;
use App\Models\Firewall;
use App\Models\Router;
use App\Models\SwitchModel;
use Illuminate\Database\Seeder;

class ConfigurationHistorySeeder extends Seeder
{
    public function run()
    {
        // Historique pour les firewalls
        $firewalls = Firewall::all();
        foreach ($firewalls as $firewall) {
            ConfigurationHistory::factory()->count(rand(2, 5))->create([
                'device_type' => 'App\\Models\\Firewall',
                'device_id' => $firewall->id,
            ]);
        }
        
        // Historique pour les routeurs
        $routers = Router::all();
        foreach ($routers as $router) {
            ConfigurationHistory::factory()->count(rand(2, 5))->create([
                'device_type' => 'App\\Models\\Router',
                'device_id' => $router->id,
            ]);
        }
        
        // Historique pour les switches
        $switches = SwitchModel::all();
        foreach ($switches as $switch) {
            ConfigurationHistory::factory()->count(rand(1, 3))->create([
                'device_type' => 'App\\Models\\SwitchModel',
                'device_id' => $switch->id,
            ]);
        }
        
        // Créer quelques backups
        ConfigurationHistory::factory()->count(15)->backup()->create();
    }
}