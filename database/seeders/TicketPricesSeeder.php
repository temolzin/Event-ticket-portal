<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketPricesSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::query()->where('name', 'Nexus Tech')->firstOrFail();

        collect([
            ['key' => 'individual', 'name' => 'Boleto individual', 'seats_per_ticket' => 1, 'early_price' => 990, 'regular_price' => 1500],
            ['key' => 'duo', 'name' => 'Boleto dupla', 'seats_per_ticket' => 2, 'early_price' => 1800, 'regular_price' => 2600],
            ['key' => 'student', 'name' => 'Boleto estudiante UTTEC', 'seats_per_ticket' => 1, 'early_price' => null, 'regular_price' => 700],
        ])->each(fn (array $type) => TicketType::query()->updateOrCreate(
            ['event_id' => $event->id, 'key' => $type['key']],
            $type + ['is_active' => true]
        ));
    }
}
