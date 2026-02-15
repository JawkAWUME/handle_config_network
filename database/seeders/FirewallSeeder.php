<?php
// database/seeders/FirewallSeeder.php
namespace Database\Seeders;

use App\Models\Firewall;
use App\Models\Site;
use App\Models\User;
use App\Models\AccessLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FirewallSeeder extends Seeder
{
    public function run()
    {
        $sites = Site::all();
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  Aucun utilisateur trouvé.');
            return;
        }
        
        $firewalls = [
            [
                'site_id' => $sites->where('name', 'Siège Social Paris')->first()->id,
                'name' => 'FW-CORE-PARIS-01',
                'brand' => 'Palo Alto',
                'model' => 'PA-3220',
                'firewall_type' => 'palo_alto',
                'ip_nms' => '10.10.1.1',
                'ip_service' => '192.168.1.1',
                'vlan_nms' => 10,
                'vlan_service' => 20,
                'username' => 'admin_fw_paris',
                'password' => Hash::make('SecurePass123!'),
                'enable_password' => Hash::make('EnablePass123!'),
                'firmware_version' => '10.1.0',
                'serial_number' => 'PAN-PA3220-001',
                'asset_tag' => 'FW-ASSET-001',
                'status' => true,
                'high_availability' => true,
                'monitoring_enabled' => true,
                'notes' => 'Firewall principal du siège',
                'security_policies' => json_encode([
                    ['name' => 'Allow-Web', 'source_zone' => 'internal', 'destination_zone' => 'external', 'action' => 'allow']
                ]),
                'licenses' => json_encode([
                    ['name' => 'Threat Prevention', 'expiration_date' => now()->addMonths(6)->format('Y-m-d')]
                ]),
            ],
        ];
        
        foreach ($firewalls as $data) {
            $firewall = Firewall::create($data);
            $this->createAccessLogs($firewall, $users);
        }
        
        Firewall::factory()->count(8)->create()->each(function ($fw) use ($users) {
            $this->createAccessLogs($fw, $users);
        });
    }
    
    private function createAccessLogs($firewall, $users)
    {
        for ($i = 0; $i < rand(3, 7); $i++) {
            AccessLog::create([
                'device_type' => Firewall::class,
                'device_id' => $firewall->id,
                'user_id' => $users->random()->id,
                'username' => $firewall->username,
                'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'action' => ['backup', 'config', 'view'][array_rand(['backup', 'config', 'view'])],
                'accessed_at' => now()->subHours(rand(1, 720)),
            ]);
        }
    }
}