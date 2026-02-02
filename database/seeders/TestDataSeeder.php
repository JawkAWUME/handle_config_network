<?php
// database/seeders/TestDataSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Site;
use App\Models\Firewall;
use App\Models\Router;
use App\Models\SwitchModel;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Vider les tables
        User::query()->delete();
        Site::query()->delete();
        Firewall::query()->delete();
        Router::query()->delete();
        SwitchModel::query()->delete();
        
        // Créer des données minimales pour les tests
        $admin = User::factory()->admin()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        
        $site = Site::factory()->create([
            'name' => 'Test Site',
            'status' => 'active',
        ]);
        
        Firewall::factory()->create([
            'site_id' => $site->id,
            'name' => 'Test Firewall',
            'status' => true,
        ]);
        
        Router::factory()->create([
            'site_id' => $site->id,
            'name' => 'Test Router',
            'status' => true,
        ]);
        
        SwitchModel::factory()->create([
            'site_id' => $site->id,
            'name' => 'Test Switch',
            'status' => true,
        ]);
    }
}