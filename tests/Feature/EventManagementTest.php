<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function eventAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

function validEventPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Concierto UTTEC',
        'description' => 'Evento de fin de cursos',
        'venue' => 'Auditorio UTTEC',
        'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
        'capacity' => 300,
        'early_quantity' => 30,
        'is_active' => '1',
    ], $overrides);
}

it('lists the events for the admin', function () {
    $event = Event::factory()->create(['name' => 'Evento Listado']);

    $this->actingAs(eventAdmin())
        ->get('/events')
        ->assertOk()
        ->assertSee('Evento Listado');
});

it('shows the event detail', function () {
    $event = Event::factory()->create(['name' => 'Evento Detalle', 'venue' => 'Salón A']);

    $this->actingAs(eventAdmin())
        ->get("/events/{$event->id}")
        ->assertOk()
        ->assertSee('Evento Detalle')
        ->assertSee('Salón A')
        ->assertSee('href="'.route('events.index').'"', false);
});

it('creates an event', function () {
    $this->actingAs(eventAdmin())
        ->post('/events', validEventPayload())
        ->assertRedirect(route('events.index'));

    $event = Event::query()->where('name', 'Concierto UTTEC')->firstOrFail();

    expect($event->venue)->toBe('Auditorio UTTEC')
        ->and($event->capacity)->toBe(300)
        ->and($event->early_quantity)->toBe(30)
        ->and($event->is_active)->toBeTrue();
});

it('rejects an event without the required fields', function () {
    $this->actingAs(eventAdmin())
        ->post('/events', [])
        ->assertSessionHasErrors(['name', 'venue', 'starts_at', 'capacity', 'early_quantity']);
});

it('rejects an event with an invalid capacity', function () {
    $this->actingAs(eventAdmin())
        ->post('/events', validEventPayload(['capacity' => 0]))
        ->assertSessionHasErrors('capacity');
});

it('rejects an early quantity bigger than the capacity', function () {
    $this->actingAs(eventAdmin())
        ->post('/events', validEventPayload(['capacity' => 10, 'early_quantity' => 11]))
        ->assertSessionHasErrors('early_quantity');
});

it('updates an event', function () {
    $event = Event::factory()->create(['name' => 'Antes', 'capacity' => 100]);

    $this->actingAs(eventAdmin())
        ->put("/events/{$event->id}", validEventPayload([
            'name' => 'Después',
            'capacity' => 200,
            'is_active' => '0',
        ]))
        ->assertRedirect(route('events.show', $event));

    $event->refresh();

    expect($event->name)->toBe('Después')
        ->and($event->capacity)->toBe(200)
        ->and($event->is_active)->toBeFalse();
});

it('locks the delete until the day after the event', function () {
    $future = Event::factory()->create(['starts_at' => now()->addDays(5)]);
    $today = Event::factory()->create(['starts_at' => now()->setTime(20, 0)]);
    $yesterday = Event::factory()->create(['starts_at' => now()->subDay()]);
    $past = Event::factory()->create(['starts_at' => now()->subDays(3)]);

    expect($future->canBeDeleted())->toBeFalse()
        ->and($today->canBeDeleted())->toBeFalse()
        ->and($yesterday->canBeDeleted())->toBeFalse()
        ->and($past->canBeDeleted())->toBeTrue();
});

it('blocks deleting an event that has not finished', function () {
    $event = Event::factory()->create(['starts_at' => now()->addDays(5)]);

    $this->actingAs(eventAdmin())
        ->delete("/events/{$event->id}")
        ->assertRedirect()
        ->assertSessionHasErrors();

    expect(Event::query()->whereKey($event->id)->exists())->toBeTrue();
});

it('deletes an event that already finished', function () {
    $event = Event::factory()->create(['starts_at' => now()->subDays(3)]);

    $this->actingAs(eventAdmin())
        ->delete("/events/{$event->id}")
        ->assertRedirect(route('events.index'));

    expect(Event::query()->whereKey($event->id)->exists())->toBeFalse();
});

it('hides the events from the seller and the manager', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)->get('/events')->assertForbidden();
    $this->actingAs($user)->get('/events/create')->assertForbidden();
    $this->actingAs($user)->post('/events', validEventPayload())->assertForbidden();
})->with([
    'seller' => UserRole::Seller,
    'manager' => UserRole::Manager,
]);
