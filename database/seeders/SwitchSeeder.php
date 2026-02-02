<?php
// database/seeders/SwitchSeeder.php

namespace Database\Seeders;

use App\Models\SwitchModel;
use App\Models\Site;
use Illuminate\Database\Seeder;

class SwitchSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les sites
        $sites = Site::all();
        
        // Switches principaux
        $switches = [
            [
                'site_id' => $sites->where('name', 'Siège Social Paris')->first()->id,
                'name' => 'SW-CORE-PARIS-01',
                'brand' => 'Cisco',
                'model' => 'Catalyst 9500',
                'ip_nms' => '10.10.1.10',
                'ip_service' => '192.168.1.10',
                'vlan_nms' => 10,
                'vlan_service' => 20,
                'ports_total' => 48,
                'ports_used' => 32,
                'firmware_version' => '16.9.1',
                'serial_number' => 'CISCO-CAT9500-001',
                'status' => true,
            ],
            [
                'site_id' => $sites->where('name', 'Datacenter Lyon')->first()->id,
                'name' => 'SW-DC-LYON-01',
                'brand' => 'Juniper',
                'model' => 'QFX5100',
                'ip_nms' => '10.10.2.10',
                'ip_service' => '192.168.2.10',
                'vlan_nms' => 30,
                'vlan_service' => 40,
                'ports_total' => 48,
                'ports_used' => 40,
                'firmware_version' => '18.4R1',
                'serial_number' => 'JNPR-QFX5100-001',
                'status' => true,
            ],
        ];
        
        foreach ($switches as $switch) {
            SwitchModel::factory()->create($switch);
        }
        
        // Switches supplémentaires générés aléatoirement
        // 2 switches core par site
        foreach ($sites as $site) {
            SwitchModel::factory()->core()->create([
                'site_id' => $site->id,
                'name' => 'SW-CORE-' . strtoupper(substr($site->city, 0, 3)) . '-01',
            ]);
        }
        
        // 3-5 switches access par site
        foreach ($sites as $site) {
            SwitchModel::factory()->count(rand(3, 5))->access()->create([
                'site_id' => $site->id,
            ]);
        }
    }
}