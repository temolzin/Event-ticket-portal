<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketPricesSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::query()->firstOrCreate(
            ['name' => 'Nexus Tech'],
            [
                'description' => 'Evento interno de Nexus Tech.',
                'venue' => 'Instalaciones Nexus Tech',
                'starts_at' => now()->addMonth(),
                'capacity' => 100,
                'early_quantity' => 30,
                'is_active' => true,
            ]
        );

        collect([
            ['key' => 'individual', 'name' => 'Boleto individual', 'early_price' => 990, 'regular_price' => 1500],
            ['key' => 'duo', 'name' => 'Boleto dupla', 'early_price' => 1800, 'regular_price' => 2600],
            ['key' => 'student', 'name' => 'Boleto estudiante UTTEC', 'early_price' => null, 'regular_price' => 700],
        ])->each(fn (array $type) => TicketType::query()->updateOrCreate(
            ['event_id' => $event->id, 'key' => $type['key']],
            $type + ['is_active' => true]
        ));
    }
}
