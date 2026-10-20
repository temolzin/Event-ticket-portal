<?php

namespace Database\Factories;

use App\Enums\ScanResult;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'manager_id' => User::factory(),
            'code' => fake()->bothify('########'),
            'result' => ScanResult::Valid,
        ];
    }
}
