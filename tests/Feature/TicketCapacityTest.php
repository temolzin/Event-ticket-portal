<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createEventWithCapacity(int $capacity): Event
{
    return Event::factory()->create(['capacity' => $capacity]);
}

function sellTickets(Event $event, int $quantity, TicketStatus $status = TicketStatus::Generated): void
{
    Ticket::factory()->count($quantity)->create([
        'event_id' => $event->id,
        'status' => $status,
    ]);
}

it('counts only active tickets against the capacity', function () {
    $event = createEventWithCapacity(100);

    sellTickets($event, 97);
    sellTickets($event, 3, TicketStatus::Cancelled);

    expect($event->countSoldSeats())->toBe(97)
        ->and($event->countAvailableSeats())->toBe(3)
        ->and($event->hasAvailableSeats())->toBeTrue();
});

it('frees a seat when a ticket is cancelled', function () {
    $event = createEventWithCapacity(100);

    sellTickets($event, 100);

    expect($event->countAvailableSeats())->toBe(0)
        ->and($event->hasAvailableSeats())->toBeFalse();

    $cancelled = $event->tickets()->take(3)->get();

    $cancelled->each(fn (Ticket $ticket) => $ticket->update(['status' => TicketStatus::Cancelled]));

    expect($event->fresh()->countSoldSeats())->toBe(97)
        ->and($event->fresh()->countAvailableSeats())->toBe(3)
        ->and($event->fresh()->hasAvailableSeats())->toBeTrue();
});

it('keeps a used ticket counting against the capacity', function () {
    $event = createEventWithCapacity(10);

    Ticket::factory()->create([
        'event_id' => $event->id,
        'status' => TicketStatus::Used,
        'checked_in_at' => now(),
    ]);

    expect($event->countSoldSeats())->toBe(1)
        ->and($event->countAvailableSeats())->toBe(9);
});

it('counts the seats defined by each active ticket type', function () {
    $event = createEventWithCapacity(5);
    $duoType = TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'duo',
        'name' => 'Boleto dupla',
        'seats_per_ticket' => 2,
    ]);
    $individualType = TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'individual',
        'seats_per_ticket' => 1,
    ]);

    Ticket::factory()->create([
        'event_id' => $event->id,
        'ticket_type_id' => $duoType->id,
    ]);
    Ticket::factory()->create([
        'event_id' => $event->id,
        'ticket_type_id' => $individualType->id,
    ]);

    expect($event->countSoldSeats())->toBe(3)
        ->and($event->countAvailableSeats())->toBe(2);

    Ticket::query()
        ->where('ticket_type_id', $duoType->id)
        ->firstOrFail()
        ->update(['status' => TicketStatus::Cancelled]);

    expect($event->fresh()->countSoldSeats())->toBe(1)
        ->and($event->fresh()->countAvailableSeats())->toBe(4);
});

it('keeps the early price quota based on tickets rather than occupied seats', function () {
    $event = createEventWithCapacity(10);
    $event->update(['early_quantity' => 2]);
    $duoType = TicketType::factory()->create([
        'event_id' => $event->id,
        'seats_per_ticket' => 2,
    ]);
    Ticket::factory()->create([
        'event_id' => $event->id,
        'ticket_type_id' => $duoType->id,
    ]);

    expect($event->countSoldSeats())->toBe(2)
        ->and($event->countSoldTickets())->toBe(1)
        ->and($event->isInEarlyPhase())->toBeTrue();
});

it('never reports a negative number of available seats', function () {
    $event = createEventWithCapacity(2);

    sellTickets($event, 5);

    expect($event->countAvailableSeats())->toBe(0);
});

it('marks a ticket as used when it was checked in', function () {
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Used, 'checked_in_at' => now()]);
    $pending = Ticket::factory()->create();

    expect($ticket->wasUsed())->toBeTrue()
        ->and($ticket->isCancelled())->toBeFalse()
        ->and($pending->wasUsed())->toBeFalse();
});

it('blocks cancelling a ticket that was already used', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $used = Ticket::factory()->create(['status' => TicketStatus::Used, 'checked_in_at' => now()]);
    $pending = Ticket::factory()->create();
    $alreadyCancelled = Ticket::factory()->create(['status' => TicketStatus::Cancelled]);

    expect($admin->can('cancel', $used))->toBeFalse()
        ->and($admin->can('cancel', $pending))->toBeTrue()
        ->and($admin->can('cancel', $alreadyCancelled))->toBeFalse();
});

it('keeps a cancelled seat free even after the ticket is gone from the active list', function () {
    $event = createEventWithCapacity(100);

    sellTickets($event, 100);
    $cancelled = $event->tickets()->take(3)->get();
    $cancelled->each(fn (Ticket $ticket) => $ticket->update(['status' => TicketStatus::Cancelled]));

    sellTickets($event, 3);

    expect($event->fresh()->countSoldSeats())->toBe(100)
        ->and($event->fresh()->countAvailableSeats())->toBe(0)
        ->and($event->fresh()->cancelledTickets()->count())->toBe(3);
});
