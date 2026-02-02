<?php
// database/seeders/SiteSeeder.php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run()
    {
        // Sites principaux
        $sites = [
            [
                'name' => 'Siège Social Paris',
                'address' => '123 Avenue des Champs-Élysées',
                'city' => 'Paris',
                'country' => 'France',
                'postal_code' => '75008',
                'phone' => '+33 1 23 45 67 89',
                'technical_contact' => 'Jean Dupont',
                'technical_email' => 'jean.dupont@entreprise.com',
                'description' => 'Siège social principal avec datacenter',
                'status' => 'active',
                'capacity' => 50,
            ],
            [
                'name' => 'Datacenter Lyon',
                'address' => '456 Rue de la République',
                'city' => 'Lyon',
                'country' => 'France',
                'postal_code' => '69002',
                'phone' => '+33 4 56 78 90 12',
                'technical_contact' => 'Marie Martin',
                'technical_email' => 'marie.martin@entreprise.com',
                'description' => 'Datacenter principal de production',
                'status' => 'active',
                'capacity' => 100,
            ],
            [
                'name' => 'Bureau Marseille',
                'address' => '789 Boulevard du Vieux-Port',
                'city' => 'Marseille',
                'country' => 'France',
                'postal_code' => '13001',
                'phone' => '+33 4 91 23 45 67',
                'technical_contact' => 'Pierre Bernard',
                'technical_email' => 'pierre.bernard@entreprise.com',
                'description' => 'Bureau régional Sud',
                'status' => 'active',
                'capacity' => 25,
            ],
        ];
        
        foreach ($sites as $site) {
            Site::create($site);
        }
        
        // Sites supplémentaires générés aléatoirement
        Site::factory()->count(7)->create();
    }
}