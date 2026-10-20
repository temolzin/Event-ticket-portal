<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'seller_id' => User::factory(),
            'buyer_name' => fake()->name(),
            'buyer_email' => fake()->unique()->safeEmail(),
            'code' => (string) Str::ulid(),
        ];
    }
}
