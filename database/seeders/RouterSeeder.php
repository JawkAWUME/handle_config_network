<?php
// database/seeders/RouterSeeder.php

namespace Database\Seeders;

use App\Models\Router;
use App\Models\Site;
use Illuminate\Database\Seeder;

class RouterSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les sites
        $sites = Site::all();
        
        // Routeurs principaux
        $routers = [
            [
                'site_id' => $sites->where('name', 'Siège Social Paris')->first()->id,
                'name' => 'RTR-CORE-PARIS-01',
                'brand' => 'Cisco',
                'model' => 'ASR 1001-X',
                'management_ip' => '10.10.1.254',
                'operating_system' => 'IOS XE',
                'serial_number' => 'CISCO-ASR1001X-001',
                'status' => true,
            ],
            [
                'site_id' => $sites->where('name', 'Datacenter Lyon')->first()->id,
                'name' => 'RTR-DC-LYON-01',
                'brand' => 'Juniper',
                'model' => 'MX204',
                'management_ip' => '10.10.2.254',
                'operating_system' => 'JunOS',
                'serial_number' => 'JNPR-MX204-001',
                'status' => true,
            ],
            [
                'site_id' => $sites->where('name', 'Bureau Marseille')->first()->id,
                'name' => 'RTR-EDGE-MRS-01',
                'brand' => 'MikroTik',
                'model' => 'CCR2004',
                'management_ip' => '10.10.3.254',
                'operating_system' => 'RouterOS',
                'serial_number' => 'MIKROTIK-CCR2004-001',
                'status' => true,
            ],
        ];
        
        foreach ($routers as $router) {
            Router::factory()->create($router);
        }
        
        // Routeurs supplémentaires générés aléatoirement
        Router::factory()->count(7)->create();
    }
}