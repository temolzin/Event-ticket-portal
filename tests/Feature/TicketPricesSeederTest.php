<?php

use App\Models\Event;
use App\Models\TicketType;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\TicketPricesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers the event separately from its ticket prices', function () {
    $startsAt = now()->addDays(60);

    $event = Event::factory()->create([
        'name' => 'Nexus Tech',
        'venue' => 'Dirección anterior',
        'capacity' => 100,
        'starts_at' => $startsAt,
    ]);

    $this->seed(EventSeeder::class);

    $event->refresh();

    expect($event->venue)->toBe('Mango MNG Jardín de Eventos Teotihuacán')
        ->and($event->capacity)->toBe(200)
        ->and($event->starts_at->toDateTimeString())->toBe($startsAt->toDateTimeString());

    $this->seed(TicketPricesSeeder::class);
    $this->seed(TicketPricesSeeder::class);

    expect(TicketType::query()->where('event_id', $event->id)->count())->toBe(3)
        ->and(Event::query()->where('name', 'Nexus Tech')->count())->toBe(1);
});

it('requires the event to exist before seeding ticket prices', function () {
    $this->seed(TicketPricesSeeder::class);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('runs the event seeder before the ticket prices seeder', function () {
    $this->seed(DatabaseSeeder::class);

    $event = Event::query()->where('name', 'Nexus Tech')->firstOrFail();

    expect($event->venue)->toBe('Mango MNG Jardín de Eventos Teotihuacán')
        ->and($event->capacity)->toBe(200)
        ->and(TicketType::query()->where('event_id', $event->id)->count())->toBe(3);
});
