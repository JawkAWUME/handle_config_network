<?php
// database/factories/SiteFactory.php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition()
    {
        return [
            'name' => $this->faker->city() . ' ' . $this->faker->randomElement(['Data Center', 'Office', 'Branch']),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => 'France',
            'postal_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'technical_contact' => $this->faker->name(),
            'technical_email' => $this->faker->companyEmail(),
            'description' => $this->faker->sentence(10),
            'status' => $this->faker->randomElement(['active', 'maintenance', 'planned']),
            'capacity' => $this->faker->numberBetween(10, 100),
            'notes' => $this->faker->text(100),
        ];
    }

    public function active()
    {
        return $this->state([
            'status' => 'active',
        ]);
    }

    public function withContacts()
    {
        return $this->state([
            'technical_contact' => $this->faker->name(),
            'technical_email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
        ]);
    }
}