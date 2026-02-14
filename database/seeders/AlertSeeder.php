<?php
// database/seeders/AlertSeeder.php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        // Alertes manuelles pour équipements existants
            $equipment = collect()
            ->concat(SwitchModel::all())
            ->concat(Router::all())
            ->concat(Firewall::all());


        foreach ($equipment as $eq) {
            Alert::factory()->create([
                'alertable_id' => $eq->id,
                'alertable_type' => get_class($eq),
                'status' => Alert::STATUS_OPEN,
                'created_by' => $users->random()->id,
            ]);
        }


        // Générer 20 alertes aléatoires
        Alert::factory()->count(20)->create();
    }
}
