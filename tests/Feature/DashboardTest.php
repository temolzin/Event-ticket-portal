<?php

use App\Enums\ScanResult;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Scan;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDashboardUser(UserRole $role): User
{
    return User::factory()->create(['role' => $role]);
}

function createDashboardEvent(array $attributes = []): Event
{
    return Event::factory()->create(array_merge([
        'name' => 'Dashboard Event',
        'capacity' => 20,
        'starts_at' => now()->addDays(3),
        'is_active' => true,
    ], $attributes));
}

it('shows active event capacity and daily check-in metrics to admins', function () {
    $admin = createDashboardUser(UserRole::Admin);
    $event = createDashboardEvent(['capacity' => 10]);
    $secondEvent = createDashboardEvent([
        'name' => 'Second Dashboard Event',
        'capacity' => 4,
        'starts_at' => now()->addDays(4),
    ]);
    $usedTicket = Ticket::factory()->create([
        'event_id' => $event->id,
        'seller_id' => $admin->id,
        'status' => TicketStatus::Used,
        'checked_in_at' => now(),
    ]);
    Ticket::factory()->create([
        'event_id' => $event->id,
        'status' => TicketStatus::Generated,
    ]);
    $cancelledTicket = Ticket::factory()->create([
        'event_id' => $event->id,
        'status' => TicketStatus::Cancelled,
    ]);
    Ticket::factory()->create(['event_id' => $secondEvent->id]);
    createDashboardEvent(['name' => 'Inactive Event', 'is_active' => false]);
    createDashboardEvent(['name' => 'Past Event', 'starts_at' => now()->subDays(3)]);

    Scan::create([
        'ticket_id' => $usedTicket->id,
        'manager_id' => $admin->id,
        'code' => 'DASHBOARD-ALLOWED',
        'result' => ScanResult::Valid,
        'created_at' => now(),
    ]);
    Scan::create([
        'ticket_id' => $cancelledTicket->id,
        'manager_id' => $admin->id,
        'code' => 'DASHBOARD-DENIED',
        'result' => ScanResult::NotFound,
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/')
        ->assertOk()
        ->assertSee('Dashboard Event')
        ->assertDontSee('Inactive Event')
        ->assertDontSee('Past Event')
        ->assertSee('Boletos vendidos')
        ->assertSee('Boletos con check-in')
        ->assertSee('data-event-id="'.$event->id.'"', false)
        ->assertSee('data-event-id="'.$secondEvent->id.'"', false)
        ->assertSee('data-event-metric="checked-in" data-value="1"', false)
        ->assertSee('data-event-metric="seats-available" data-value="8"', false)
        ->assertSee('data-event-metric="allowed-today" data-value="1"', false)
        ->assertSee('data-event-metric="denied-today" data-value="1"', false)
        ->assertSee('data-event-metric="allowed-today" data-value="0"', false)
        ->assertSee('data-event-metric="denied-today" data-value="0"', false)
        ->assertSee('DASHBOARD-ALLOWED')
        ->assertSee('DASHBOARD-DENIED')
        ->assertSee('Crear evento');

    expect($event->tickets()->whereNotNull('checked_in_at')->count())->toBe(1);
});

it('shows a seller their own sales alongside global event capacity', function () {
    $seller = createDashboardUser(UserRole::Seller);
    $event = createDashboardEvent(['capacity' => 20]);
    Ticket::factory()->create([
        'event_id' => $event->id,
        'seller_id' => $seller->id,
        'status' => TicketStatus::Sent,
        'sent_at' => now(),
    ]);
    Ticket::factory()->create([
        'event_id' => $event->id,
        'status' => TicketStatus::Sent,
        'sent_at' => now(),
    ]);

    $this->actingAs($seller)
        ->get('/')
        ->assertOk()
        ->assertSee('Tus boletos vendidos')
        ->assertSee('2 / 20')
        ->assertSee('data-event-metric="your-sales" data-value="1"', false)
        ->assertSee('data-event-metric="tickets-sent" data-value="1"', false)
        ->assertSee('Registrar una venta')
        ->assertSee('Ventas recientes');
});

it('limits manager dashboard check-in activity to their own scans', function () {
    $manager = createDashboardUser(UserRole::Manager);
    $otherManager = createDashboardUser(UserRole::Manager);
    $event = createDashboardEvent();
    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'status' => TicketStatus::Sent,
        'sent_at' => now(),
    ]);

    Scan::create([
        'ticket_id' => $ticket->id,
        'manager_id' => $manager->id,
        'code' => 'MY-DASHBOARD-SCAN',
        'result' => ScanResult::Valid,
        'created_at' => now(),
    ]);
    Scan::create([
        'ticket_id' => $ticket->id,
        'manager_id' => $otherManager->id,
        'code' => 'OTHER-MANAGER-SCAN',
        'result' => ScanResult::NotFound,
        'created_at' => now(),
    ]);

    $this->actingAs($manager)
        ->get('/')
        ->assertOk()
        ->assertSee('MY-DASHBOARD-SCAN')
        ->assertDontSee('OTHER-MANAGER-SCAN')
        ->assertSee('Accesos permitidos hoy')
        ->assertSee('data-event-metric="allowed-today" data-value="1"', false)
        ->assertSee('data-event-metric="denied-today" data-value="0"', false)
        ->assertSee('Abrir check-in')
        ->assertSee('Ver bitácora de escaneos');
});

it('shows an empty state when there are no active upcoming events', function () {
    $admin = createDashboardUser(UserRole::Admin);
    createDashboardEvent(['is_active' => false]);
    createDashboardEvent(['starts_at' => now()->subDays(3)]);

    $this->actingAs($admin)
        ->get('/')
        ->assertOk()
        ->assertSee('Aún no hay eventos activos');
});
