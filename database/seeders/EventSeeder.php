<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::query()->firstOrNew(['name' => 'Nexus Tech']);
        $event->fill([
            'description' => 'Evento interno de Nexus Tech.',
            'venue' => 'Mango MNG Jardín de Eventos Teotihuacán',
            'capacity' => 200,
            'early_quantity' => 30,
            'is_active' => true,
        ]);

        if (! $event->exists) {
            $event->starts_at = now()->addMonth();
        }

        $event->save();
    }
}
