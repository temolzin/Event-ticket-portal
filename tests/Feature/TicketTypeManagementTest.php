<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function ticketTypeAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

it('lists the prices of the event', function () {
    $event = Event::factory()->create(['name' => 'Evento con precios']);
    TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'individual',
        'name' => 'Boleto individual',
        'early_price' => 990,
        'regular_price' => 1500,
    ]);

    $this->actingAs(ticketTypeAdmin())
        ->get("/events/{$event->id}/prices")
        ->assertOk()
        ->assertSee('Boleto individual')
        ->assertSee('990.00')
        ->assertSee('1,500.00');
});

it('adds a ticket type from the prices screen', function () {
    $event = Event::factory()->create();

    $this->actingAs(ticketTypeAdmin())
        ->post('/ticket-types', [
            'event_id' => $event->id,
            'name' => 'Boleto dupla',
            'seats_per_ticket' => 2,
            'early_price' => 990,
            'regular_price' => 1500,
            'is_active' => '1',
        ])
        ->assertRedirect(route('events.prices', $event));

    $type = TicketType::query()->where('event_id', $event->id)->firstOrFail();

    expect($type->name)->toBe('Boleto dupla')
        ->and($type->seats_per_ticket)->toBe(2)
        ->and($type->key)->toBe('boleto-dupla')
        ->and((float) $type->early_price)->toBe(990.0)
        ->and((float) $type->regular_price)->toBe(1500.0)
        ->and($type->is_active)->toBeTrue();
});

it('accepts a ticket type without an early price', function () {
    $event = Event::factory()->create();

    $this->actingAs(ticketTypeAdmin())
        ->post('/ticket-types', [
            'event_id' => $event->id,
            'name' => 'Boleto estudiante',
            'early_price' => '',
            'regular_price' => 700,
        ])
        ->assertRedirect(route('events.prices', $event));

    $type = TicketType::query()->where('event_id', $event->id)->firstOrFail();

    expect($type->early_price)->toBeNull()
        ->and((float) $type->regular_price)->toBe(700.0);
});

it('rejects a ticket type without a name or a regular price', function () {
    $event = Event::factory()->create();

    $this->actingAs(ticketTypeAdmin())
        ->post('/ticket-types', [
            'event_id' => $event->id,
            'name' => '',
            'regular_price' => '',
        ])
        ->assertSessionHasErrors(['name', 'regular_price']);
});

it('rejects a repeated ticket type name on the same event', function () {
    $event = Event::factory()->create();
    TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'boleto-individual',
        'name' => 'Boleto individual',
    ]);

    $this->actingAs(ticketTypeAdmin())
        ->post('/ticket-types', [
            'event_id' => $event->id,
            'name' => 'Boleto individual',
            'regular_price' => 1500,
        ])
        ->assertSessionHasErrors('key');
});

it('toggles a ticket type on and off', function () {
    $event = Event::factory()->create();
    $type = TicketType::factory()->create(['event_id' => $event->id, 'is_active' => true]);

    $this->actingAs(ticketTypeAdmin())
        ->patch("/events/{$event->id}/prices/{$type->id}")
        ->assertRedirect(route('events.prices', $event));

    expect($type->fresh()->is_active)->toBeFalse();

    $this->actingAs(ticketTypeAdmin())
        ->patch("/events/{$event->id}/prices/{$type->id}")
        ->assertRedirect(route('events.prices', $event));

    expect($type->fresh()->is_active)->toBeTrue();
});

it('deletes a ticket type that has no sales', function () {
    $event = Event::factory()->create();
    $type = TicketType::factory()->create(['event_id' => $event->id]);

    $this->actingAs(ticketTypeAdmin())
        ->delete("/events/{$event->id}/prices/{$type->id}")
        ->assertRedirect(route('events.prices', $event));

    expect(TicketType::query()->whereKey($type->id)->exists())->toBeFalse();
});

it('blocks deleting a ticket type that already has sales', function () {
    $event = Event::factory()->create();
    $type = TicketType::factory()->create(['event_id' => $event->id]);
    Ticket::factory()->create([
        'event_id' => $event->id,
        'ticket_type_id' => $type->id,
    ]);

    $this->actingAs(ticketTypeAdmin())
        ->delete("/events/{$event->id}/prices/{$type->id}")
        ->assertForbidden();

    expect(TicketType::query()->whereKey($type->id)->exists())->toBeTrue();
});

it('creates an event together with its ticket prices', function () {
    $this->actingAs(ticketTypeAdmin())
        ->post('/events', [
            'name' => 'Evento con boletos',
            'venue' => 'Auditorio',
            'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'capacity' => 100,
            'early_quantity' => 30,
            'is_active' => '1',
            'ticket_types' => [
                ['name' => 'Boleto individual', 'early_price' => 990, 'regular_price' => 1500],
                ['name' => 'Boleto dupla', 'seats_per_ticket' => 2, 'early_price' => 1800, 'regular_price' => 2600],
            ],
        ])
        ->assertRedirect(route('events.index'));

    $event = Event::query()->where('name', 'Evento con boletos')->firstOrFail();

    expect($event->ticketTypes)->toHaveCount(2)
        ->and($event->ticketTypes->pluck('key')->sort()->values()->all())
        ->toBe(['boleto-dupla', 'boleto-individual'])
        ->and($event->ticketTypes->firstWhere('key', 'boleto-dupla')->seats_per_ticket)->toBe(2);
});

it('adds ticket prices when updating an event', function () {
    $event = Event::factory()->create();
    TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'boleto-individual',
        'name' => 'Boleto individual',
    ]);

    $this->actingAs(ticketTypeAdmin())
        ->put("/events/{$event->id}", [
            'name' => $event->name,
            'venue' => $event->venue,
            'starts_at' => $event->starts_at->format('Y-m-d\TH:i'),
            'capacity' => $event->capacity,
            'early_quantity' => $event->early_quantity,
            'is_active' => '1',
            'ticket_types' => [
                ['name' => 'Boleto individual', 'regular_price' => 1500],
                ['name' => 'Boleto estudiante', 'regular_price' => 700],
            ],
        ])
        ->assertRedirect(route('events.show', $event));

    expect($event->fresh()->ticketTypes)->toHaveCount(2)
        ->and($event->fresh()->ticketTypes->pluck('name')->all())
        ->toContain('Boleto individual', 'Boleto estudiante');
});

it('hides the prices screen from the seller and the manager', function (UserRole $role) {
    $event = Event::factory()->create();

    $this->actingAs(User::factory()->create(['role' => $role]))
        ->get("/events/{$event->id}/prices")
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => $role]))
        ->post('/ticket-types', [
            'event_id' => $event->id,
            'name' => 'Boleto individual',
            'regular_price' => 1500,
        ])
        ->assertForbidden();
})->with([
    'seller' => UserRole::Seller,
    'manager' => UserRole::Manager,
]);
