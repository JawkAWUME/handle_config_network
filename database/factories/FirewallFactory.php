<?php
// database/factories/FirewallFactory.php

namespace Database\Factories;

use App\Models\Firewall;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class FirewallFactory extends Factory
{
    protected $model = Firewall::class;

    public function definition()
    {
        $brand = $this->faker->randomElement(['Palo Alto', 'Fortinet', 'Cisco', 'Check Point', 'Juniper']);
        $model = match($brand) {
            'Palo Alto' => $this->faker->randomElement(['PA-220', 'PA-820', 'PA-850', 'PA-3220']),
            'Fortinet' => $this->faker->randomElement(['FortiGate 60F', 'FortiGate 100F', 'FortiGate 600E']),
            'Cisco' => $this->faker->randomElement(['ASA 5506-X', 'ASA 5516-X', 'Firepower 1010']),
            'Check Point' => $this->faker->randomElement(['15600', '14800', '4600']),
            'Juniper' => $this->faker->randomElement(['SRX300', 'SRX340', 'SRX380']),
            default => 'Generic',
        };

        return [
            'site_id' => Site::factory(),
            'name' => $this->faker->randomElement(['FW-', 'FW-EDGE-', 'FW-CORE-']) . $this->faker->bothify('?##'),
            'brand' => $brand,
            'model' => $model,
            'firewall_type' => $this->faker->randomElement(['palo_alto', 'fortinet', 'cisco_asa', 'checkpoint', 'other']),
            'ip_nms' => $this->faker->ipv4(),
            'ip_service' => $this->faker->ipv4(),
            'vlan_nms' => $this->faker->numberBetween(1, 100),
            'vlan_service' => $this->faker->numberBetween(101, 200),
            'username' => 'admin',
            'password' => bcrypt('password123'),
            'enable_password' => bcrypt('enable123'),
            'firmware_version' => $this->faker->randomElement(['9.1.0', '10.0.0', '10.1.0', '10.2.0']),
            'serial_number' => $this->faker->uuid(),
            'asset_tag' => 'ASSET-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->boolean(80),
            'high_availability' => $this->faker->boolean(30),
            'ha_peer_id' => null,
            'monitoring_enabled' => true,
            'last_backup' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional()->text(200),
            'security_policies' => json_encode($this->generateSecurityPolicies()),
            'nat_rules' => json_encode($this->generateNatRules()),
            'vpn_configuration' => json_encode($this->generateVpnConfig()),
            'licenses' => json_encode($this->generateLicenses()),
        ];
    }

    private function generateSecurityPolicies(): array
    {
        $policies = [];
        $policyCount = $this->faker->numberBetween(5, 20);
        
        for ($i = 1; $i <= $policyCount; $i++) {
            $policies[] = [
                'name' => "Policy-$i",
                'source_zone' => $this->faker->randomElement(['trust', 'untrust', 'dmz']),
                'destination_zone' => $this->faker->randomElement(['trust', 'untrust', 'dmz']),
                'source_address' => $this->faker->ipv4(),
                'destination_address' => $this->faker->ipv4(),
                'application' => $this->faker->randomElement(['web-browsing', 'ssh', 'http', 'https']),
                'action' => $this->faker->randomElement(['allow', 'deny']),
                'enabled' => $this->faker->boolean(90),
                'description' => $this->faker->sentence(),
            ];
        }
        
        return $policies;
    }

    private function generateNatRules(): array
    {
        $rules = [];
        $ruleCount = $this->faker->numberBetween(2, 10);
        
        for ($i = 1; $i <= $ruleCount; $i++) {
            $rules[] = [
                'name' => "NAT-Rule-$i",
                'type' => $this->faker->randomElement(['static', 'dynamic', 'port']),
                'original_source' => $this->faker->ipv4(),
                'translated_source' => $this->faker->ipv4(),
                'enabled' => true,
            ];
        }
        
        return $rules;
    }

    private function generateVpnConfig(): array
    {
        return [
            'enabled' => $this->faker->boolean(40),
            'type' => $this->faker->randomElement(['site-to-site', 'remote-access']),
            'peers' => [
                [
                    'name' => 'VPN-Peer-1',
                    'ip' => $this->faker->ipv4(),
                    'psk' => $this->faker->password(16, 32),
                ]
            ],
        ];
    }

    private function generateLicenses(): array
    {
        return [
            [
                'type' => 'threat',
                'expiration_date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
                'status' => 'active',
            ],
            [
                'type' => 'url-filtering',
                'expiration_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
                'status' => 'active',
            ],
        ];
    }

    public function withHa()
    {
        return $this->state(function (array $attributes) {
            return [
                'high_availability' => true,
            ];
        });
    }
}