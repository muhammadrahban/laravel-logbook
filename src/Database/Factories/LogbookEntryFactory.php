<?php

namespace Rahban\LaravelLogbook\Database\Factories;

use Rahban\LaravelLogbook\Models\LogbookEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookEntryFactory extends Factory
{
    protected $model = LogbookEntry::class;

    public function definition()
    {
        $type = $this->faker->randomElement(['request', 'event']);

        if ($type === 'request') {
            return [
                'type' => $type,
                'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']),
                'url' => $this->faker->url(),
                'endpoint' => '/api/' . $this->faker->word(),
                'status_code' => $this->faker->randomElement([200, 201, 400, 401, 404, 500]),
                'response_time' => $this->faker->randomFloat(2, 10, 2000),
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'user_id' => $this->faker->optional()->numberBetween(1, 100),
                'token_id' => $this->faker->optional()->regexify('[A-Za-z0-9]{8}***'),
                'request_headers' => [
                    'accept' => ['application/json'],
                    'content-type' => ['application/json'],
                ],
                'response_headers' => [
                    'content-type' => ['application/json'],
                ],
                'request_body' => $this->faker->optional()->text(200),
                'response_body' => $this->faker->optional()->text(200),
                'metadata' => [
                    'route_name' => $this->faker->optional()->word(),
                ],
                'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        } else {
            return [
                'type' => $type,
                'event_name' => $this->faker->randomElement(['user.login', 'user.logout', 'payment.processed', 'email.sent']),
                'event_data' => [
                    'key' => $this->faker->word(),
                    'value' => $this->faker->sentence(),
                ],
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'user_id' => $this->faker->optional()->numberBetween(1, 100),
                'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        }
    }

    public function request()
    {
        return $this->state(['type' => 'request']);
    }

    public function event()
    {
        return $this->state(['type' => 'event']);
    }
}
