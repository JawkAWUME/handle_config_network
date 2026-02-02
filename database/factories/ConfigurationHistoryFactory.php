<?php
// database/factories/ConfigurationHistoryFactory.php

namespace Database\Factories;

use App\Models\ConfigurationHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigurationHistoryFactory extends Factory
{
    protected $model = ConfigurationHistory::class;

    public function definition()
    {
        $deviceTypes = ['App\\Models\\Firewall', 'App\\Models\\Router', 'App\\Models\\SwitchModel'];
        $deviceType = $this->faker->randomElement($deviceTypes);
        
        return [
            'device_type' => $deviceType,
            'device_id' => $this->faker->numberBetween(1, 50),
            'configuration' => $this->generateConfiguration(),
            'config_size' => $this->faker->numberBetween(1000, 50000),
            'config_checksum' => md5($this->faker->text(100)),
            'user_id' => User::factory(),
            'change_type' => $this->faker->randomElement([
                'create', 'update', 'backup', 'restore', 'auto_backup', 'manual_backup'
            ]),
            'notes' => $this->faker->optional(0.7)->sentence(),
            'ip_address' => $this->faker->ipv4(),
            'change_summary' => $this->faker->optional()->text(200),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    private function generateConfiguration(): string
    {
        $lines = [];
        $lineCount = $this->faker->numberBetween(50, 200);
        
        for ($i = 0; $i < $lineCount; $i++) {
            $lines[] = $this->faker->randomElement([
                'interface GigabitEthernet0/' . $i,
                ' ip address ' . $this->faker->ipv4() . ' ' . $this->faker->randomElement(['255.255.255.0', '255.255.255.128']),
                ' description ' . $this->faker->words(3, true),
                ' no shutdown',
                'router ospf ' . $this->faker->numberBetween(1, 100),
                ' network ' . $this->faker->ipv4() . ' 0.0.0.255 area 0',
                '!',
                'access-list ' . $this->faker->numberBetween(1, 199) . ' permit ip any any',
                'line vty 0 4',
                ' login local',
                ' transport input ssh',
            ]);
        }
        
        return implode("\n", $lines);
    }

    public function forFirewall()
    {
        return $this->state([
            'device_type' => 'App\\Models\\Firewall',
        ]);
    }

    public function forRouter()
    {
        return $this->state([
            'device_type' => 'App\\Models\\Router',
        ]);
    }

    public function backup()
    {
        return $this->state([
            'change_type' => $this->faker->randomElement(['backup', 'auto_backup', 'manual_backup']),
        ]);
    }
}