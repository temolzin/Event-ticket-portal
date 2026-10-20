<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'venue' => fake()->city().' Convention Center',
            'starts_at' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'capacity' => fake()->numberBetween(50, 500),
            'price' => fake()->randomFloat(2, 10, 500),
            'is_active' => true,
        ];
    }
}
