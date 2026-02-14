<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Désactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Vider les tables
        DB::table('users')->truncate();
        DB::table('sites')->truncate();
        DB::table('firewalls')->truncate();
        DB::table('routers')->truncate();
        DB::table('switches')->truncate();
        DB::table('configuration_histories')->truncate();
        DB::table('access_logs')->truncate();
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Exécuter les seeders
        $this->call([
            UserSeeder::class,
            SiteSeeder::class,
            FirewallSeeder::class,
            RouterSeeder::class,
            SwitchSeeder::class,
            ConfigurationHistorySeeder::class,
            AccessLogSeeder::class,
            AlertSeeder::class,
            BackupSeeder::class,
        ]);
    }
}