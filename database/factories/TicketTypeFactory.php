<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'key' => Str::lower(fake()->unique()->lexify('????')),
            'name' => fake()->words(2, true),
            'seats_per_ticket' => 1,
            'early_price' => fake()->randomFloat(2, 10, 500),
            'regular_price' => fake()->randomFloat(2, 500, 900),
            'is_active' => true,
        ];
    }
}
