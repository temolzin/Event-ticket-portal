<?php

use App\Enums\ScanResult;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Scan;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function checkInManager(): User
{
    return User::factory()->create(['role' => UserRole::Manager]);
}

function checkInTicket(array $attributes = []): Ticket
{
    return Ticket::factory()->create($attributes + [
        'status' => TicketStatus::Sent,
        'sent_at' => now(),
    ]);
}

it('lets the manager open the scanner', function () {
    $this->actingAs(checkInManager())
        ->get('/check-in')
        ->assertOk()
        ->assertSee('Escanear boleto')
        ->assertSee('Código del boleto');
});

it('lets the admin open the scanner', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get('/check-in')
        ->assertOk();
});

it('allows the access and marks the ticket as used', function () {
    $manager = checkInManager();
    $ticket = checkInTicket();

    $this->actingAs($manager)
        ->post('/check-in', ['code' => $ticket->code])
        ->assertRedirect();

    $ticket->refresh();

    expect($ticket->status)->toBe(TicketStatus::Used)
        ->and($ticket->checked_in_at)->not->toBeNull();

    $scan = Scan::query()->firstOrFail();

    expect($scan->result)->toBe(ScanResult::Valid)
        ->and($scan->ticket_id)->toBe($ticket->id)
        ->and($scan->manager_id)->toBe($manager->id)
        ->and($scan->code)->toBe($ticket->code);
});

it('shows the green panel for a valid ticket', function () {
    $manager = checkInManager();
    $ticket = checkInTicket();

    $this->actingAs($manager)->post('/check-in', ['code' => $ticket->code])->assertRedirect();

    $this->actingAs($manager)
        ->get(route('check-in.result', Scan::query()->firstOrFail()))
        ->assertOk()
        ->assertSee('Acceso permitido')
        ->assertSee($ticket->buyer_name)
        ->assertSee($ticket->event->name);
});

it('denies the access for a cancelled ticket', function () {
    $manager = checkInManager();
    $ticket = checkInTicket(['status' => TicketStatus::Cancelled]);

    $this->actingAs($manager)->post('/check-in', ['code' => $ticket->code])->assertRedirect();

    $scan = Scan::query()->firstOrFail();

    expect($scan->result)->toBe(ScanResult::Cancelled)
        ->and($ticket->fresh()->status)->toBe(TicketStatus::Cancelled)
        ->and($ticket->fresh()->checked_in_at)->toBeNull();

    $this->actingAs($manager)
        ->get(route('check-in.result', $scan))
        ->assertOk()
        ->assertSee('Acceso denegado');
});

it('denies the access when the ticket was already used', function () {
    $manager = checkInManager();
    $ticket = checkInTicket(['status' => TicketStatus::Used, 'checked_in_at' => now()]);

    $this->actingAs($manager)->post('/check-in', ['code' => $ticket->code])->assertRedirect();

    $scan = Scan::query()->firstOrFail();

    expect($scan->result)->toBe(ScanResult::AlreadyUsed)
        ->and($ticket->fresh()->checked_in_at)->not->toBeNull();
});

it('denies the access when the code does not exist', function () {
    $manager = checkInManager();

    $this->actingAs($manager)
        ->post('/check-in', ['code' => 'CODIGO-QUE-NO-EXISTE'])
        ->assertRedirect();

    $scan = Scan::query()->firstOrFail();

    expect($scan->result)->toBe(ScanResult::NotFound)
        ->and($scan->ticket_id)->toBeNull()
        ->and($scan->code)->toBe('CODIGO-QUE-NO-EXISTE');

    $this->actingAs($manager)
        ->get(route('check-in.result', $scan))
        ->assertOk()
        ->assertSee(__('Invalid or unknown QR code'));
});

it('only allows the first scan of the same ticket', function () {
    $manager = checkInManager();
    $ticket = checkInTicket();

    $this->actingAs($manager)->post('/check-in', ['code' => $ticket->code])->assertRedirect();
    $this->actingAs($manager)->post('/check-in', ['code' => $ticket->code])->assertRedirect();

    expect(Scan::query()->get()->pluck('result')->all())->toBe([
        ScanResult::Valid,
        ScanResult::AlreadyUsed,
    ]);
});

it('accepts the code typed in lowercase', function () {
    $ticket = checkInTicket();

    $this->actingAs(checkInManager())
        ->post('/check-in', ['code' => Str::lower($ticket->code)])
        ->assertRedirect();

    expect(Scan::query()->firstOrFail()->result)->toBe(ScanResult::Valid);
});

it('returns the result panel to the camera scanner', function () {
    $manager = checkInManager();
    $ticket = checkInTicket();

    $this->actingAs($manager)
        ->post('/check-in', ['code' => $ticket->code], ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSee('Acceso permitido')
        ->assertDontSee('<html');
});

it('shows a designed error panel when an unknown QR code is scanned', function () {
    $this->actingAs(checkInManager())
        ->post('/check-in', ['code' => 'QR-DESCONOCIDO'], ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSee(__('Invalid or unknown QR code'))
        ->assertSee(__('Scan another ticket'))
        ->assertSee('border-error-200');
});

it('rejects malformed QR codes that exceed the supported length', function () {
    $this->actingAs(checkInManager())
        ->post('/check-in', ['code' => str_repeat('X', 41)], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    expect(Scan::query()->count())->toBe(0);
});

it('requires a code to validate', function () {
    $this->actingAs(checkInManager())
        ->post('/check-in', ['code' => ''])
        ->assertSessionHasErrors('code');

    expect(Scan::query()->count())->toBe(0);
});

it('logs every attempt', function () {
    $manager = checkInManager();
    $ticket = checkInTicket();

    $this->actingAs($manager)->post('/check-in', ['code' => $ticket->code]);
    $this->actingAs($manager)->post('/check-in', ['code' => 'OTRO-CODIGO']);

    $this->actingAs($manager)
        ->get('/check-in/log')
        ->assertOk()
        ->assertSee($ticket->code)
        ->assertSee('OTRO-CODIGO')
        ->assertSee('Accesos permitidos')
        ->assertSee('Accesos denegados');
});

it('groups scan log entries by event and keeps unknown codes together', function () {
    $manager = checkInManager();
    $firstEvent = Event::factory()->create(['name' => 'Evento Uno']);
    $secondEvent = Event::factory()->create(['name' => 'Evento Dos']);
    $firstTicket = checkInTicket(['event_id' => $firstEvent->id]);
    $secondTicket = checkInTicket(['event_id' => $secondEvent->id]);

    Scan::create([
        'ticket_id' => $firstTicket->id,
        'manager_id' => $manager->id,
        'code' => 'CODIGO-EVENTO-UNO',
        'result' => ScanResult::Valid,
    ]);
    Scan::create([
        'ticket_id' => $secondTicket->id,
        'manager_id' => $manager->id,
        'code' => 'CODIGO-EVENTO-DOS',
        'result' => ScanResult::Valid,
    ]);
    Scan::create([
        'ticket_id' => null,
        'manager_id' => $manager->id,
        'code' => 'CODIGO-SIN-EVENTO',
        'result' => ScanResult::NotFound,
    ]);

    $this->actingAs($manager)
        ->get(route('check-in.log'))
        ->assertOk()
        ->assertSeeInOrder([
            'Evento Dos',
            'CODIGO-EVENTO-DOS',
            'Evento Uno',
            'CODIGO-EVENTO-UNO',
            __('Unknown event'),
            'CODIGO-SIN-EVENTO',
        ]);
});

it('filters the log by result', function () {
    $manager = checkInManager();
    $ticket = checkInTicket();

    $this->actingAs($manager)->post('/check-in', ['code' => $ticket->code]);
    $this->actingAs($manager)->post('/check-in', ['code' => 'OTRO-CODIGO']);

    $this->actingAs($manager)
        ->get('/check-in/log?result=not_found')
        ->assertOk()
        ->assertSeeInOrder(['value="not_found"', 'selected'], false)
        ->assertSee('OTRO-CODIGO')
        ->assertDontSee($ticket->code);
});
