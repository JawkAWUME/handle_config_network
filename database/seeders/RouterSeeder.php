<?php
// database/seeders/RouterSeeder.php
namespace Database\Seeders;

use App\Models\Router;
use App\Models\Site;
use App\Models\User;
use App\Models\AccessLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RouterSeeder extends Seeder
{
    public function run()
    {
        $sites = Site::all();
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  Aucun utilisateur trouvé.');
            return;
        }
        
        $routers = [
            [
                'site_id' => $sites->where('name', 'Siège Social Paris')->first()->id,
                'name' => 'RTR-CORE-PARIS-01',
                'brand' => 'Cisco',
                'model' => 'ASR 1001-X',
                'management_ip' => '10.10.1.254',
                'vlan_nms' => 10,
                'vlan_service' => 20,
                
                // ✅ CREDENTIALS
                'username' => 'admin_rtr_paris',
                'password' => Hash::make('RouterPass123!'),
                
                // ✅ METADATA
                'operating_system' => 'IOS XE 17.3.1',
                'serial_number' => 'CISCO-ASR1001X-001',
                'asset_tag' => 'RTR-ASSET-001',
                'status' => true,
                'notes' => 'Routeur core principal. Gère le routage inter-VLAN et BGP.',
                
                // ✅ INTERFACES
                'interfaces' => json_encode([
                    [
                        'name' => 'GigabitEthernet0/0/0',
                        'ip_address' => '192.168.1.254',
                        'subnet_mask' => '/24',
                        'description' => 'LAN Principal',
                        'status' => 'up',
                        'vlan' => 10,
                        'speed' => '1G'
                    ],
                    [
                        'name' => 'GigabitEthernet0/0/1',
                        'ip_address' => '203.0.113.1',
                        'subnet_mask' => '/30',
                        'description' => 'WAN Internet',
                        'status' => 'up',
                        'vlan' => null,
                        'speed' => '1G'
                    ],
                    [
                        'name' => 'GigabitEthernet0/0/2',
                        'ip_address' => '10.0.0.1',
                        'subnet_mask' => '/30',
                        'description' => 'Link to Lyon',
                        'status' => 'up',
                        'vlan' => null,
                        'speed' => '1G'
                    ],
                ]),
                
                // ✅ ROUTING PROTOCOLS
                'routing_protocols' => json_encode(['OSPF', 'BGP', 'EIGRP']),
            ],
            
            [
                'site_id' => $sites->where('name', 'Datacenter Lyon')->first()->id,
                'name' => 'RTR-DC-LYON-01',
                'brand' => 'Juniper',
                'model' => 'MX204',
                'management_ip' => '10.10.2.254',
                'vlan_nms' => 30,
                'vlan_service' => 40,
                'username' => 'admin_jnpr_lyon',
                'password' => Hash::make('JuniperPass456!'),
                'operating_system' => 'JunOS 21.2R1',
                'serial_number' => 'JNPR-MX204-001',
                'asset_tag' => 'RTR-ASSET-002',
                'status' => true,
                'notes' => 'Routeur datacenter. MPLS et redondance.',
                'interfaces' => json_encode([
                    [
                        'name' => 'ge-0/0/0',
                        'ip_address' => '192.168.2.254',
                        'subnet_mask' => '/24',
                        'description' => 'DC LAN',
                        'status' => 'up',
                        'vlan' => 30,
                        'speed' => '1G'
                    ],
                ]),
                'routing_protocols' => json_encode(['OSPF', 'MPLS']),
            ],
        ];
        
        foreach ($routers as $data) {
            $router = Router::create($data);
            $this->createAccessLogs($router, $users);
        }
        
        Router::factory()->count(7)->create()->each(function ($router) use ($users) {
            $this->createAccessLogs($router, $users);
        });
        
        $this->command->info('✅ Routeurs créés avec access logs');
    }
    
    private function createAccessLogs($router, $users)
    {
        for ($i = 0; $i < rand(3, 7); $i++) {
            AccessLog::create([
                'device_type' => Router::class,
                'device_id' => $router->id,
                'user_id' => $users->random()->id,
                'username' => $router->username,
                'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'action' => ['backup', 'config_change', 'view', 'interface_update'][array_rand(['backup', 'config_change', 'view', 'interface_update'])],
                'accessed_at' => now()->subHours(rand(1, 720)),
            ]);
        }
    }
}