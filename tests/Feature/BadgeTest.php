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
    $seller = actingRole(UserRole::Seller);
    $event = Event::factory()->create(['name' => 'My Badge Event']);
    createBadgeTicket($seller, $event);
    $otherEvent = Event::factory()->create(['name' => 'Other Seller Event']);
    createBadgeTicket(actingRole(UserRole::Seller), $otherEvent);

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
    $response = $this->actingAs($admin)
        ->get(route('badges.events.print', $event))
        ->assertOk()
        ->assertSee('Active Client')
        ->assertSee('Client Without Photo')
        ->assertDontSee('Cancelled Client')
        ->assertDontSee("/tickets/{$activeTicket->id}/photo", false)
        ->assertSee(asset('images/badges/company-logo.png'), false);

    $html = $response->getContent();

    expect(substr_count($response->getContent(), 'class="badge__photo"'))->toBe(1)
        ->and(substr_count($response->getContent(), 'class="badge__photo-placeholder"'))->toBe(1)
        ->and($ticketWithoutPhoto->buyer_photo_path)->toBeNull()
        ->and($html)->toContain('background: #f3efe6;')
        ->and($html)->toContain('#087e8b')
        ->and($html)->toContain('#d98752')
        ->and($html)->toContain('#102a32')
        ->and($html)->toContain('clip-path: polygon(')
        ->and($html)->toContain('transform: scaleX(-1);')
        ->and($html)->toContain('z-index: 2;')
        ->and($html)->not->toContain('background: #ffffff;')
        ->and($html)->not->toContain('#8c1725')
        ->and($html)->not->toContain('#f28c28')
        ->and($html)->not->toContain('#1037ff')
        ->and($html)->not->toContain('#f2aa28')
        ->and($html)->toContain('white-space: nowrap;')
        ->and($html)->toContain('max-width: 28mm !important;')
        ->and($html)->toContain('max-height: 8mm !important;')
        ->and($html)->toContain('badge-pair')
        ->and($html)->toContain(__('Connecting ideas, creating the future.'))
        ->and($html)->toContain(asset('images/badges/company-logo.png'))
        ->and(strpos($html, 'class="badge__details"'))
        ->toBeLessThan(strpos($html, 'class="badge__footer"'));
});

it('prints a matching tech-themed keepsake back for every badge', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $event = Event::factory()->create([
        'name' => 'Nexus Tech',
        'venue' => 'Guadalajara',
        'starts_at' => now()->setDate(2026, 11, 18)->setTime(19, 30),
    ]);
    createBadgeTicket($admin, $event, ['buyer_name' => 'Badge Client']);

    $html = $this->actingAs($admin)
        ->get(route('badges.events.print', $event))
        ->assertOk()
        ->assertSee(__('Connecting ideas, creating the future.'))
        ->assertSee(__('I was part of'))
        ->assertSee('Nexus Tech')
        ->assertSee(__('Root Heim Company'))
        ->getContent();

    $backStart = strpos($html, 'class="badge badge--back badge-pair__back"');
    $backEnd = strpos($html, '</article>', $backStart);
    $back = substr($html, $backStart, $backEnd - $backStart);

    expect(substr_count($html, 'class="badge badge--front"'))->toBe(1)
        ->and(substr_count($html, 'class="badge badge--back badge-pair__back"'))->toBe(1)
        ->and(substr_count($html, '<main class="badge-pair">'))->toBe(1)
        ->and($html)->toContain('Guadalajara')
        ->and($back)->not->toContain('Guadalajara')
        ->and($back)->not->toContain('23 ene. 2027')
        ->and($html)->toContain('href="https://rootheim.com/"')
        ->and($html)->toContain('rootheim.com')
        ->and($html)->toContain('grid-template-columns: repeat(2, 54mm);')
        ->and($html)->toContain('page-break-inside: avoid;')
        ->and($html)->toContain(__('The front and back of each badge print together on the same sheet.'));
});

it('keeps every badge front and back paired without forcing separate sheets', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $event = Event::factory()->create();

    foreach (range(1, 10) as $number) {
        createBadgeTicket($admin, $event, ['buyer_name' => "Client {$number}"]);
    }

    $html = $this->actingAs($admin)
        ->get(route('badges.events.print', $event))
        ->assertOk()
        ->getContent();

    expect(substr_count($html, 'class="badge badge--front"'))->toBe(10)
        ->and(substr_count($html, 'class="badge badge--back badge-pair__back"'))->toBe(10)
        ->and(substr_count($html, '<main class="badge-pair">'))->toBe(10)
        ->and($html)->not->toContain('badge-grid--page-break')
        ->and($html)->not->toContain('For two-sided badges, print double-sided');
});

it('prints only the selected badges for an event', function () {
    $seller = actingRole(UserRole::Seller);
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
    $seller = actingRole(UserRole::Seller);
    $ticket = createBadgeTicket($seller, Event::factory()->create());

    $this->actingAs($seller)
        ->get(route('badges.tickets.print', $ticket->encryptedDownloadKey()))
        ->assertOk()
        ->assertSee($ticket->buyer_name)
        ->assertSee(asset('images/badges/company-logo.png'), false);

    actingRole(UserRole::Seller);
        $this->get(route('badges.tickets.print', $ticket->encryptedDownloadKey()))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('badges.tickets.print', $ticket->encryptedDownloadKey()))
        ->assertOk();
});

it('rejects selected tickets outside the seller event scope', function () {
    $seller = actingRole(UserRole::Seller);
    $event = Event::factory()->create();
    $foreignTicket = createBadgeTicket(actingRole(UserRole::Seller), $event);

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