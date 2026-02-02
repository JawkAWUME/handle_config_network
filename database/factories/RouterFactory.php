<?php
// database/factories/RouterFactory.php

namespace Database\Factories;

use App\Models\Router;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouterFactory extends Factory
{
    protected $model = Router::class;

    public function definition()
    {
        $brand = $this->faker->randomElement(['Cisco', 'Juniper', 'Huawei', 'MikroTik']);
        $model = match($brand) {
            'Cisco' => $this->faker->randomElement(['ISR 4431', 'ASR 1001-X', 'CSR 1000V']),
            'Juniper' => $this->faker->randomElement(['MX204', 'MX480', 'SRX380']),
            'Huawei' => $this->faker->randomElement(['NE40E', 'AR2200']),
            'MikroTik' => $this->faker->randomElement(['CCR2004', 'RB4011']),
            default => 'Generic',
        };

        return [
            'site_id' => Site::factory(),
            'name' => $this->faker->randomElement(['RTR-', 'CORE-', 'EDGE-']) . $this->faker->bothify('?##'),
            'brand' => $brand,
            'model' => $model,
            'management_ip' => $this->faker->ipv4(),
            'interfaces' => json_encode($this->generateInterfaces()),
            'routing_protocols' => json_encode($this->generateRoutingProtocols()),
            'username' => 'admin',
            'password' => bcrypt('password123'),
            'operating_system' => match($brand) {
                'Cisco' => 'IOS XE',
                'Juniper' => 'JunOS',
                'Huawei' => 'VRP',
                'MikroTik' => 'RouterOS',
                default => 'Generic OS',
            },
            'serial_number' => $this->faker->uuid(),
            'asset_tag' => 'ASSET-' . $this->faker->unique()->numberBetween(2000, 2999),
            'status' => $this->faker->boolean(85),
            'last_backup' => $this->faker->optional(0.6)->dateTimeBetween('-2 months', 'now'),
            'notes' => $this->faker->optional()->text(150),
        ];
    }

    private function generateInterfaces(): array
    {
        $interfaces = [];
        $interfaceCount = $this->faker->numberBetween(4, 12);
        
        for ($i = 0; $i < $interfaceCount; $i++) {
            $interfaces[] = [
                'name' => "GigabitEthernet0/$i",
                'ip_address' => $this->faker->ipv4(),
                'subnet_mask' => $this->faker->randomElement(['255.255.255.0', '255.255.255.128', '255.255.0.0']),
                'description' => $this->faker->randomElement(['Uplink to Core', 'DMZ', 'User LAN', 'Server VLAN']),
                'status' => $this->faker->randomElement(['up', 'down', 'administratively down']),
                'speed' => $this->faker->randomElement(['1G', '10G', '100M']),
            ];
        }
        
        return $interfaces;
    }

    private function generateRoutingProtocols(): array
    {
        return [
            'bgp' => [
                'enabled' => $this->faker->boolean(40),
                'as_number' => $this->faker->numberBetween(64512, 65535),
                'neighbors' => $this->faker->numberBetween(1, 5),
            ],
            'ospf' => [
                'enabled' => $this->faker->boolean(70),
                'area' => '0.0.0.0',
                'process_id' => $this->faker->numberBetween(1, 100),
            ],
            'static_routes' => $this->faker->numberBetween(2, 10),
        ];
    }

    public function withInterfaces($count = 8)
    {
        return $this->state(function (array $attributes) use ($count) {
            $interfaces = [];
            for ($i = 0; $i < $count; $i++) {
                $interfaces[] = [
                    'name' => "GigabitEthernet0/$i",
                    'ip_address' => $this->faker->ipv4(),
                    'subnet_mask' => '255.255.255.0',
                    'status' => 'up',
                ];
            }
            
            return [
                'interfaces' => json_encode($interfaces),
            ];
        });
    }
}