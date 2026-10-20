<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Ticket;
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

it('never reports a negative number of available seats', function () {
    $event = createEventWithCapacity(2);

    sellTickets($event, 5);

    expect($event->countAvailableSeats())->toBe(0);
});

it('lists the cancelled tickets with the buyer and the seller who sold them', function () {
    $event = createEventWithCapacity(100);
    $seller = User::factory()->create(['role' => UserRole::Seller]);

    Ticket::factory()->create([
        'event_id' => $event->id,
        'seller_id' => $seller->id,
        'buyer_name' => 'Ana López',
        'buyer_email' => 'ana@example.test',
        'status' => TicketStatus::Cancelled,
    ]);

    Ticket::factory()->create([
        'event_id' => $event->id,
        'seller_id' => $seller->id,
        'buyer_name' => 'Luis Pérez',
        'status' => TicketStatus::Generated,
    ]);

    $cancelled = $event->cancelledTickets()->with('seller')->get();

    expect($cancelled)->toHaveCount(1)
        ->and($cancelled->first()->buyer_name)->toBe('Ana López')
        ->and($cancelled->first()->buyer_email)->toBe('ana@example.test')
        ->and($cancelled->first()->seller->id)->toBe($seller->id)
        ->and($cancelled->first()->isCancelled())->toBeTrue()
        ->and($event->activeTickets)->toHaveCount(1);
});

it('scopes tickets by status and seller', function () {
    $event = createEventWithCapacity(100);
    $seller = User::factory()->create(['role' => UserRole::Seller]);
    $otherSeller = User::factory()->create(['role' => UserRole::Seller]);

    Ticket::factory()->count(2)->create(['event_id' => $event->id, 'seller_id' => $seller->id]);
    Ticket::factory()->create(['event_id' => $event->id, 'seller_id' => $seller->id, 'status' => TicketStatus::Cancelled]);
    Ticket::factory()->create(['event_id' => $event->id, 'seller_id' => $otherSeller->id]);

    expect(Ticket::query()->active()->count())->toBe(3)
        ->and(Ticket::query()->cancelled()->count())->toBe(1)
        ->and(Ticket::query()->soldBy($seller->id)->count())->toBe(3);
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
