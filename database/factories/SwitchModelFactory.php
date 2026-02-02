<?php
// database/factories/SwitchFactory.php

namespace Database\Factories;

use App\Models\SwitchModel;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SwitchModelFactory extends Factory
{
    protected $model = SwitchModel::class;

    public function definition()
    {
        $brand = $this->faker->randomElement(['Cisco', 'Juniper', 'HP', 'Aruba', 'MikroTik']);
        $model = match($brand) {
            'Cisco' => $this->faker->randomElement(['Catalyst 9300', 'Catalyst 9500', 'Nexus 93180']),
            'Juniper' => $this->faker->randomElement(['EX4300', 'EX4600', 'QFX5100']),
            'HP' => $this->faker->randomElement(['Aruba 2930F', 'Aruba 3810']),
            'Aruba' => $this->faker->randomElement(['CX 6300', 'CX 6400']),
            'MikroTik' => $this->faker->randomElement(['CRS354', 'CRS328']),
            default => 'Generic',
        };

        $portsTotal = $this->faker->randomElement([24, 48]);
        $portsUsed = $this->faker->numberBetween(5, $portsTotal * 0.7);

        return [
            'site_id' => Site::factory(),
            'name' => $this->faker->randomElement(['SW-', 'ACCESS-', 'CORE-']) . $this->faker->bothify('?##'),
            'brand' => $brand,
            'model' => $model,
            'ip_nms' => $this->faker->ipv4(),
            'ip_service' => $this->faker->ipv4(),
            'vlan_nms' => $this->faker->numberBetween(1, 100),
            'vlan_service' => $this->faker->numberBetween(101, 200),
            'username' => 'admin',
            'password' => bcrypt('password123'),
            'ports_total' => $portsTotal,
            'ports_used' => $portsUsed,
            'firmware_version' => $this->faker->randomElement(['16.9.1', '15.2.4', '10.0.8']),
            'serial_number' => $this->faker->uuid(),
            'asset_tag' => 'ASSET-' . $this->faker->unique()->numberBetween(3000, 3999),
            'status' => $this->faker->boolean(90),
            'last_backup' => $this->faker->optional(0.5)->dateTimeBetween('-3 months', 'now'),
            'notes' => $this->faker->optional()->text(150),
        ];
    }

    public function core()
    {
        return $this->state([
            'name' => 'CORE-' . $this->faker->bothify('?##'),
            'ports_total' => 48,
            'ports_used' => $this->faker->numberBetween(20, 40),
        ]);
    }

    public function access()
    {
        return $this->state([
            'name' => 'ACCESS-' . $this->faker->bothify('?##'),
            'ports_total' => 24,
            'ports_used' => $this->faker->numberBetween(10, 20),
        ]);
    }
}