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
                // ✅ Ajout des champs manquants attendus par la vue/contrôleur
                'ip_nms' => '10.10.1.254',
                'ip_service' => '192.168.1.254',
                'management_ip' => '172.16.0.1',
                'vlan_nms' => 10,
                'vlan_service' => 20,
                'username' => 'admin_rtr_paris',
                'password' => Hash::make('RouterPass123!'),
                'enable_password' => Hash::make('EnablePass123!'),
                'serial_number' => 'CISCO-ASR1001X-001',
                'asset_tag' => 'RTR-ASSET-001',
                'status' => true,
                // ✅ Champs pour le tableau de la vue
                'interfaces_count' => 24,
                'interfaces_up_count' => 22,
                // ✅ Champ pour le modal
                'configuration' => "! Cisco ASR 1001-X Configuration\nhostname RTR-CORE-PARIS-01\n",
                'notes' => 'Routeur core principal',
            ],
        ];
        
        foreach ($routers as $data) {
            $router = Router::create($data);
            $this->createAccessLogs($router, $users);
        }
        
        // Créer 7 routeurs supplémentaires avec la factory
        Router::factory()->count(7)->create()->each(function ($router) use ($users) {
            // ✅ Ajouter les champs manquants après création
            $router->update([
                'interfaces_count' => rand(12, 48),
                'interfaces_up_count' => rand(10, $router->interfaces_count ?? 24),
            ]);
            $this->createAccessLogs($router, $users);
        });
        
        $this->command->info('✅ Routeurs créés avec access logs');
    }
    
    private function createAccessLogs($router, $users)
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
                'device_type' => Router::class,
                'device_id' => $router->id,
                'user_id' => $users->random()->id,
                'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'user_agent' => $this->getRandomUserAgent(),
                'action' => $actions[array_rand($actions)],
                'method' => ['GET', 'POST', 'PUT'][array_rand(['GET', 'POST', 'PUT'])],
                'url' => '/api/routers/' . $router->id,
                'result' => rand(0, 10) > 1 ? AccessLog::RESULT_SUCCESS : AccessLog::RESULT_FAILED,
                'response_code' => rand(0, 10) > 1 ? 200 : 403,
                'response_time' => rand(50, 500) / 100,
                'parameters' => json_encode(['router_id' => $router->id]),
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