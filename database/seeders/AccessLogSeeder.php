<?php
// database/seeders/AccessLogSeeder.php

namespace Database\Seeders;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccessLogSeeder extends Seeder
{
    public function run()
    {
        // Logs de connexion réussis
        AccessLog::factory()->count(100)->successful()->create();
        
        // Quelques échecs de connexion
        AccessLog::factory()->count(15)->failed()->create();
        
        // Logs suspects
        AccessLog::factory()->count(5)->suspicious()->create();
        
        // Mélanger les dates pour avoir une distribution réaliste
        $this->randomizeDates();
    }
    
    private function randomizeDates()
    {
        $logs = AccessLog::all();
        
        foreach ($logs as $log) {
            $log->created_at = now()->subDays(rand(0, 30))
                ->subHours(rand(0, 23))
                ->subMinutes(rand(0, 59));
            $log->save();
        }
    }
}