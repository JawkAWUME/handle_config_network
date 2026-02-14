<?php
// database/factories/AlertFactory.php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\User;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition()
    {
        $alertableClasses = [
            SwitchModel::class,
            Router::class,
            Firewall::class,
            Site::class,
        ];

        $alertableType = $this->faker->randomElement($alertableClasses);

        return [
            'alertable_id' => $alertableType::factory(),
            'alertable_type' => $alertableType,
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(),
            'severity' => $this->faker->randomElement([
                Alert::SEVERITY_INFO,
                Alert::SEVERITY_WARNING,
                Alert::SEVERITY_CRITICAL
            ]),
            'status' => $this->faker->randomElement([
                Alert::STATUS_OPEN,
                Alert::STATUS_RESOLVED,
                Alert::STATUS_IGNORED
            ]),
            'triggered_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'resolved_at' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Scope pour alertes critiques
     */
    public function critical()
    {
        return $this->state([
            'severity' => Alert::SEVERITY_CRITICAL,
        ]);
    }

    /**
     * Scope pour alertes ouvertes
     */
    public function open()
    {
        return $this->state([
            'status' => Alert::STATUS_OPEN,
        ]);
    }
}
