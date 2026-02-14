<?php
// database/seeders/BackupSeeder.php

namespace Database\Seeders;

use App\Models\Backup;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use App\Models\User;
use Illuminate\Database\Seeder;

class BackupSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $equipment = collect()
        ->concat(SwitchModel::all())
        ->concat(Router::all())
        ->concat(Firewall::all());


        // Backups manuels pour chaque équipement
       foreach ($equipment as $eq) {
            Backup::factory()->create([
                'backupable_id' => $eq->id,
                'backupable_type' => get_class($eq),
                'status' => Backup::STATUS_SUCCESS,
                'created_by' => $users->random()->id,
            ]);
        }


        // Générer 15 backups aléatoires
        Backup::factory()->count(15)->create();
    }
}
