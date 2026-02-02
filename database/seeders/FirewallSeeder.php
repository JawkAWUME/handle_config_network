<?php
// database/seeders/FirewallSeeder.php

namespace Database\Seeders;

use App\Models\Firewall;
use App\Models\Site;
use Illuminate\Database\Seeder;

class FirewallSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les sites
        $sites = Site::all();
        
        // Firewalls principaux
        $firewalls = [
            [
                'site_id' => $sites->where('name', 'Siège Social Paris')->first()->id,
                'name' => 'FW-CORE-PARIS-01',
                'brand' => 'Palo Alto',
                'model' => 'PA-3220',
                'firewall_type' => 'palo_alto',
                'ip_nms' => '10.10.1.1',
                'ip_service' => '192.168.1.1',
                'vlan_nms' => 10,
                'vlan_service' => 20,
                'firmware_version' => '10.1.0',
                'serial_number' => 'PAN-PA3220-001',
                'status' => true,
                'high_availability' => true,
                'monitoring_enabled' => true,
            ],
            [
                'site_id' => $sites->where('name', 'Datacenter Lyon')->first()->id,
                'name' => 'FW-DC-LYON-01',
                'brand' => 'Fortinet',
                'model' => 'FortiGate 600E',
                'firewall_type' => 'fortinet',
                'ip_nms' => '10.10.2.1',
                'ip_service' => '192.168.2.1',
                'vlan_nms' => 30,
                'vlan_service' => 40,
                'firmware_version' => '7.0.0',
                'serial_number' => 'FGT-600E-001',
                'status' => true,
                'high_availability' => false,
                'monitoring_enabled' => true,
            ],
        ];
        
        foreach ($firewalls as $firewall) {
            Firewall::factory()->create($firewall);
        }
        
        // Firewalls supplémentaires générés aléatoirement
        Firewall::factory()->count(8)->create();
        
        // Configurer les paires HA
        $this->configureHaPairs();
    }
    
    private function configureHaPairs()
    {
        // Créer des paires HA pour certains firewalls
        $firewalls = Firewall::where('high_availability', true)->get();
        
        foreach ($firewalls as $firewall) {
            // Créer un pair HA
            $haPeer = Firewall::factory()->create([
                'site_id' => $firewall->site_id,
                'name' => str_replace('-01', '-02', $firewall->name),
                'brand' => $firewall->brand,
                'model' => $firewall->model,
                'ha_peer_id' => $firewall->id,
            ]);
            
            // Mettre à jour le firewall principal avec l'ID du pair
            $firewall->update(['ha_peer_id' => $haPeer->id]);
        }
    }
}