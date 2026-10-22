<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

function badgeSeller(): User
{
    return User::factory()->create(['role' => UserRole::Seller]);
}

function createBadgeTicket(User $seller, Event $event, array $attributes = []): Ticket
{
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);

    return Ticket::factory()->create($attributes + [
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
        'seller_id' => $seller->id,
        'buyer_name' => fake()->name(),
    ]);
}

it('lists only events with active tickets the current seller can access', function () {
    $seller = badgeSeller();
    $event = Event::factory()->create(['name' => 'My Badge Event']);
    createBadgeTicket($seller, $event);
    $otherEvent = Event::factory()->create(['name' => 'Other Seller Event']);
    createBadgeTicket(badgeSeller(), $otherEvent);

    $this->actingAs($seller)
        ->get(route('badges.index'))
        ->assertOk()
        ->assertSee('My Badge Event')
        ->assertDontSee('Other Seller Event');
});

it('prints every active event badge and excludes cancelled tickets', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $event = Event::factory()->create(['name' => 'Badge Print Event']);
    $activeTicket = createBadgeTicket($admin, $event, ['buyer_name' => 'Active Client']);
    $activeTicket->storeBuyerPhoto(UploadedFile::fake()->image('client.png'));
    $activeTicket->save();
    $ticketWithoutPhoto = createBadgeTicket($admin, $event, ['buyer_name' => 'Client Without Photo']);
    createBadgeTicket($admin, $event, [
        'buyer_name' => 'Cancelled Client',
        'status' => TicketStatus::Cancelled,
    ]);

    $this->actingAs($admin)
        ->get(route('badges.events.print', $event))
        ->assertOk()
        ->assertSee('Active Client')
        ->assertSee('Client Without Photo')
        ->assertDontSee('Cancelled Client')
        ->assertSee(route('tickets.photo', $activeTicket), false)
        ->assertSee(__('Print badges'))
        ->assertSee(__('No photo'))
        ->assertSee('Badge Print Event')
        ->assertSee($ticketWithoutPhoto->ticketType->name);
});

it('prints only the selected badges for an event', function () {
    $seller = badgeSeller();
    $event = Event::factory()->create();
    $selectedTicket = createBadgeTicket($seller, $event, ['buyer_name' => 'Selected Client']);
    createBadgeTicket($seller, $event, ['buyer_name' => 'Unselected Client']);

    $this->actingAs($seller)
        ->post(route('badges.events.print-selected', $event), [
            'ticket_ids' => [$selectedTicket->id],
        ])
        ->assertOk()
        ->assertSee('Selected Client')
        ->assertDontSee('Unselected Client');
});

it('prints an individual badge for its seller and admin only', function () {
    $seller = badgeSeller();
    $ticket = createBadgeTicket($seller, Event::factory()->create());

    $this->actingAs($seller)
        ->get(route('badges.tickets.print', $ticket))
        ->assertOk()
        ->assertSee($ticket->buyer_name);

    $this->actingAs(badgeSeller())
        ->get(route('badges.tickets.print', $ticket))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('badges.tickets.print', $ticket))
        ->assertOk();
});

it('rejects selected tickets outside the seller event scope', function () {
    $seller = badgeSeller();
    $event = Event::factory()->create();
    $foreignTicket = createBadgeTicket(badgeSeller(), $event);

    $this->actingAs($seller)
        ->post(route('badges.events.print-selected', $event), [
            'ticket_ids' => [$foreignTicket->id],
        ])
        ->assertNotFound();
});

it('blocks managers from the badge module', function () {
    $manager = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($manager)
        ->get(route('badges.index'))
        ->assertForbidden();
});
