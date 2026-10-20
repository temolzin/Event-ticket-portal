<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
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
            'price' => fake()->randomFloat(2, 10, 900),
            'code' => (string) Str::ulid(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(fn (Ticket $ticket) => $ticket->ticket_type_id ??= TicketType::factory()
            ->create(['event_id' => $ticket->event_id])
            ->id);
    }
}
