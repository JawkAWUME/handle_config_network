<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Créer un admin principal
        User::factory()->admin()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@network.com',
            'password' => bcrypt('admin123'),
        ]);
        
        // Créer un technicien
        User::factory()->technician()->create([
            'name' => 'Technicien Réseau',
            'email' => 'tech@network.com',
            'password' => bcrypt('tech123'),
        ]);
        
        // Créer des utilisateurs supplémentaires
        User::factory()->count(5)->create();
    }
}