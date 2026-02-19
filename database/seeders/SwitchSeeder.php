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
                'username' => 'admin_sw_paris',
                'password' => Hash::make('SwitchPass123!'),
                'ports_total' => 48,
                'ports_used' => 32,
                'firmware_version' => '16.9.1',
                'serial_number' => 'CISCO-CAT9500-001',
                'asset_tag' => 'SW-ASSET-001',
                'status' => true,
                'notes' => 'Switch core du siège',
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
                'asset_tag' => 'SW-' . strtoupper(substr($site->city, 0, 3)) . '-001',
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
        $actions = [
            AccessLog::TYPE_BACKUP,
            AccessLog::TYPE_VIEW,
            AccessLog::TYPE_UPDATE,
            AccessLog::TYPE_LOGIN,
        ];
        
        for ($i = 0; $i < rand(3, 7); $i++) {
            $createdAt = now()->subHours(rand(1, 720));
            
            AccessLog::create([
                'device_type' => SwitchModel::class,
                'device_id' => $switch->id,
                'user_id' => $users->random()->id,
                'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'user_agent' => $this->getRandomUserAgent(),
                'action' => $actions[array_rand($actions)],
                'method' => ['GET', 'POST', 'PUT'][array_rand(['GET', 'POST', 'PUT'])],
                'url' => '/api/switches/' . $switch->id,
                'result' => rand(0, 10) > 1 ? AccessLog::RESULT_SUCCESS : AccessLog::RESULT_FAILED,
                'response_code' => rand(0, 10) > 1 ? 200 : 403,
                'response_time' => rand(50, 500) / 100,
                'parameters' => json_encode(['switch_id' => $switch->id]),
                'browser' => ['Chrome', 'Firefox', 'Safari'][array_rand(['Chrome', 'Firefox', 'Safari'])],
                'platform' => ['Windows', 'macOS', 'Linux'][array_rand(['Windows', 'macOS', 'Linux'])],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
    
    private function getRandomUserAgent()
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        ];
        return $agents[array_rand($agents)];
    }
}