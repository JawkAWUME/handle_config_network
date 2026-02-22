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
                // ✅ Ajout des champs attendus par la vue
                'security_policies_count' => 150,
                'cpu' => 42,
                'memory' => 67,
                'configuration' => "! Palo Alto PA-3220 Configuration\nhostname FW-CORE-PARIS-01\n",
                'notes' => 'Firewall principal du siège',
            ],
        ];
        
        foreach ($firewalls as $data) {
            $firewall = Firewall::create($data);
            $this->createAccessLogs($firewall, $users);
        }
        
        // Créer 8 firewalls supplémentaires avec la factory
        Firewall::factory()->count(8)->create()->each(function ($fw) use ($users) {
            // ✅ Ajouter les champs manquants après création
            $fw->update([
                'security_policies_count' => rand(50, 300),
                'cpu' => rand(20, 85),
                'memory' => rand(30, 90),
            ]);
            $this->createAccessLogs($fw, $users);
        });
        
        $this->command->info('✅ Firewalls créés avec access logs');
    }
    
    private function createAccessLogs($firewall, $users)
    {
        $actions = [
            AccessLog::TYPE_BACKUP,
            AccessLog::TYPE_VIEW,
            AccessLog::TYPE_UPDATE,
            AccessLog::TYPE_LOGIN,
        ];
        
        $results = [
            AccessLog::RESULT_SUCCESS,
            AccessLog::RESULT_SUCCESS,
            AccessLog::RESULT_SUCCESS,
            AccessLog::RESULT_FAILED,
        ];
        
        for ($i = 0; $i < rand(3, 7); $i++) {
            $createdAt = now()->subHours(rand(1, 720));
            
            AccessLog::create([
                // ✅ Polymorphic relation
                'device_type' => Firewall::class,
                'device_id' => $firewall->id,
                
                // ✅ User info
                'user_id' => $users->random()->id,
                'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'user_agent' => $this->getRandomUserAgent(),
                'action' => $actions[array_rand($actions)],
                'method' => $this->getRandomMethod(),
                'url' => '/api/firewalls/' . $firewall->id,
                'result' => $results[array_rand($results)],
                'response_code' => rand(0, 10) > 1 ? 200 : 403,
                'response_time' => rand(50, 500) / 100,
                'parameters' => json_encode([
                    'firewall_id' => $firewall->id,
                    'firewall_name' => $firewall->name,
                ]),
                'browser' => $this->getRandomBrowser(),
                'platform' => $this->getRandomPlatform(),                
                // ✅ Connection details
                'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'user_agent' => $this->getRandomUserAgent(),
                
                // ✅ Action details
                'action' => $actions[array_rand($actions)],
                'method' => $this->getRandomMethod(),
                'url' => '/api/firewalls/' . $firewall->id,
                
                // ✅ Response details
                'result' => $results[array_rand($results)],
                'response_code' => rand(0, 10) > 1 ? 200 : 403,
                'response_time' => rand(50, 500) / 100, // 0.5 à 5 secondes
                
                // ✅ Optional metadata
                'parameters' => json_encode([
                    'firewall_id' => $firewall->id,
                    'firewall_name' => $firewall->name,
                    'action_type' => 'configuration'
                ]),
                
                // ✅ Browser/Platform info (sera enrichi automatiquement par le modèle)
                'browser' => $this->getRandomBrowser(),
                'platform' => $this->getRandomPlatform(),
                // ✅ Timestamps
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
    
    private function getRandomUserAgent()
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
        ];
        return $agents[array_rand($agents)];
    }
    
    private function getRandomMethod()
    {
        return ['GET', 'POST', 'PUT', 'PATCH'][array_rand(['GET', 'POST', 'PUT', 'PATCH'])];
    }
    
    private function getRandomBrowser()
    {
        return ['Chrome', 'Firefox', 'Safari', 'Edge'][array_rand(['Chrome', 'Firefox', 'Safari', 'Edge'])];
    }
    
    private function getRandomPlatform()
    {
        return ['Windows', 'macOS', 'Linux'][array_rand(['Windows', 'macOS', 'Linux'])];
    }
}