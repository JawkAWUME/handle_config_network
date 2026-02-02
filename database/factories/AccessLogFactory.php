<?php
// database/factories/AccessLogFactory.php

namespace Database\Factories;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessLogFactory extends Factory
{
    protected $model = AccessLog::class;

    public function definition()
    {
        $actions = AccessLog::getConstants('TYPE_');
        $results = AccessLog::getConstants('RESULT_');
        
        $deviceTypes = ['App\\Models\\Firewall', 'App\\Models\\Router', 'App\\Models\\SwitchModel'];
        $deviceType = $this->faker->randomElement($deviceTypes);
        
        return [
            'user_id' => User::factory(),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'url' => $this->faker->url(),
            'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'action' => $this->faker->randomElement($actions),
            'device_type' => $deviceType,
            'device_id' => $this->faker->numberBetween(1, 50),
            'parameters' => json_encode(['param1' => $this->faker->word(), 'param2' => $this->faker->numberBetween(1, 100)]),
            'response_code' => $this->faker->randomElement([200, 201, 400, 401, 403, 404, 500]),
            'response_time' => $this->faker->randomFloat(2, 0.1, 5.0),
            'result' => $this->faker->randomElement($results),
            'error_message' => $this->faker->optional(0.3)->sentence(),
            'referrer' => $this->faker->optional()->url(),
            'country' => $this->faker->countryCode(),
            'city' => $this->faker->city(),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'platform' => $this->faker->randomElement(['Windows', 'macOS', 'Linux']),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function successful()
    {
        return $this->state([
            'result' => AccessLog::RESULT_SUCCESS,
            'response_code' => 200,
        ]);
    }

    public function failed()
    {
        return $this->state([
            'result' => AccessLog::RESULT_FAILED,
            'response_code' => $this->faker->randomElement([400, 401, 403, 500]),
            'error_message' => $this->faker->sentence(),
        ]);
    }

    public function suspicious()
    {
        return $this->state([
            'ip_address' => $this->faker->randomElement(['10.0.0.1', '192.168.0.1', '172.16.0.1']),
            'result' => AccessLog::RESULT_DENIED,
            'response_code' => 403,
            'action' => AccessLog::TYPE_LOGIN,
        ]);
    }
}