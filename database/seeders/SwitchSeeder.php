<?php
// database/seeders/SwitchSeeder.php

namespace Database\Seeders;

use App\Models\SwitchModel;
use App\Models\Site;
use App\Models\User;
use App\Models\AccessLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SwitchSeeder extends Seeder
{
    public function run()
    {
        $sites = Site::all();
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  Aucun utilisateur trouvé.');
            return;
        }
        
        // Switches principaux avec TOUTES les données
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
                
                // ✅ CREDENTIALS
                'username' => 'admin_sw_paris',
                'password' => Hash::make('SwitchPass123!'),
                
                // ✅ METADATA
                'ports_total' => 48,
                'ports_used' => 32,
                'firmware_version' => '16.9.1',
                'serial_number' => 'CISCO-CAT9500-001',
                'asset_tag' => 'SW-ASSET-001',
                'status' => true,
                'notes' => 'Switch core du siège. Backbone du réseau.',
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
                'username' => 'admin_sw_lyon',
                'password' => Hash::make('JuniperSW456!'),
                'ports_total' => 48,
                'ports_used' => 40,
                'firmware_version' => '18.4R1',
                'serial_number' => 'JNPR-QFX5100-001',
                'asset_tag' => 'SW-ASSET-002',
                'status' => true,
                'notes' => 'Switch datacenter principal. 40GbE uplinks.',
            ],
        ];
        
        foreach ($switches as $data) {
            $switch = SwitchModel::create($data);
            $this->createAccessLogs($switch, $users);
        }
        
        // 2 switches core par site
        foreach ($sites as $site) {
            $switch = SwitchModel::factory()->create([
                'site_id' => $site->id,
                'name' => 'SW-CORE-' . strtoupper(substr($site->city, 0, 3)) . '-01',
                'username' => 'admin_sw_' . strtolower($site->city),
                'password' => Hash::make('Pass' . rand(1000, 9999) . '!'),
                'asset_tag' => 'SW-' . strtoupper(substr($site->city, 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'notes' => 'Switch core ' . $site->name,
            ]);
            $this->createAccessLogs($switch, $users);
        }
        
        // 3-5 switches access par site
        foreach ($sites as $site) {
            $count = rand(3, 5);
            for ($i = 1; $i <= $count; $i++) {
                $switch = SwitchModel::factory()->create([
                    'site_id' => $site->id,
                    'name' => 'SW-ACCESS-' . strtoupper(substr($site->city, 0, 3)) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'username' => 'admin_access',
                    'password' => Hash::make('AccessPass' . rand(100, 999) . '!'),
                    'asset_tag' => 'SW-ACC-' . strtoupper(substr($site->city, 0, 3)) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'notes' => 'Switch access étage ' . $i,
                ]);
                $this->createAccessLogs($switch, $users);
            }
        }
        
        $this->command->info('✅ Switches créés avec access logs');
    }
    
    private function createAccessLogs($switch, $users)
    {
        for ($i = 0; $i < rand(3, 7); $i++) {
            AccessLog::create([
                'device_type' => SwitchModel::class,
                'device_id' => $switch->id,
                'user_id' => $users->random()->id,
                'username' => $switch->username,
                'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'action' => ['backup', 'vlan_config', 'port_config', 'view'][array_rand(['backup', 'vlan_config', 'port_config', 'view'])],
                'accessed_at' => now()->subHours(rand(1, 720)),
            ]);
        }
    }
}