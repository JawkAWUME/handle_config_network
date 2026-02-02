<?php
// database/factories/UserFactory.php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'remember_token' => Str::random(10),
            'role' => $this->faker->randomElement(['admin', 'technician', 'viewer']),
            'department' => $this->faker->randomElement(['IT', 'Network', 'Security']),
            'phone' => $this->faker->phoneNumber(),
            'is_active' => true,
        ];
    }

    public function admin()
    {
        return $this->state([
            'role' => 'admin',
        ]);
    }

    public function technician()
    {
        return $this->state([
            'role' => 'technician',
        ]);
    }
}