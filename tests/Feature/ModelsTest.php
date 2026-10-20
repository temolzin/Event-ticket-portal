<?php

use App\Enums\ScanResult;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Scan;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores an event with every field from the flow', function () {
    $event = Event::factory()->create([
        'name' => 'Rock Night',
        'description' => 'Concierto de rock',
        'venue' => 'Auditorio Municipal',
        'starts_at' => '2026-12-31 20:00:00',
        'capacity' => 100,
        'early_quantity' => 30,
        'is_active' => false,
    ]);

    $stored = Event::find($event->id);

    expect($stored->name)->toBe('Rock Night')
        ->and($stored->description)->toBe('Concierto de rock')
        ->and($stored->venue)->toBe('Auditorio Municipal')
        ->and($stored->starts_at->format('Y-m-d H:i'))->toBe('2026-12-31 20:00')
        ->and($stored->capacity)->toBe(100)
        ->and($stored->early_quantity)->toBe(30)
        ->and($stored->is_active)->toBeFalse();
});

it('stores every price phase of a ticket type', function () {
    $event = Event::factory()->create();

    $individual = TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'individual',
        'name' => 'Boleto individual',
        'early_price' => 990,
        'regular_price' => 1500,
        'is_active' => true,
    ]);

    $student = TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'student',
        'name' => 'Boleto estudiante UTTEC',
        'early_price' => null,
        'regular_price' => 700,
        'is_active' => true,
    ]);

    expect($event->ticketTypes)->toHaveCount(2)
        ->and((float) $individual->fresh()->early_price)->toBe(990.0)
        ->and((float) $individual->fresh()->regular_price)->toBe(1500.0)
        ->and($student->fresh()->early_price)->toBeNull()
        ->and((float) $student->fresh()->regular_price)->toBe(700.0)
        ->and($individual->fresh()->event->id)->toBe($event->id);
});

it('keeps one ticket type key per event', function () {
    $event = Event::factory()->create();
    $type = TicketType::factory()->create(['event_id' => $event->id, 'key' => 'individual']);

    expect(fn () => TicketType::factory()->create(['event_id' => $event->id, 'key' => $type->key]))
        ->toThrow(QueryException::class);
});

it('relates an event with many tickets', function () {
    $event = Event::factory()->create();
    $seller = User::factory()->create();

    Ticket::factory()->count(2)->for($event)->for($seller, 'seller')->create();

    expect($event->tickets)->toHaveCount(2)
        ->and($seller->tickets)->toHaveCount(2)
        ->and($event->tickets->first()->event->id)->toBe($event->id)
        ->and($event->tickets->first()->seller->id)->toBe($seller->id);
});

it('relates a ticket with the ticket type that holds its price', function () {
    $event = Event::factory()->create();
    $type = TicketType::factory()->create(['event_id' => $event->id, 'name' => 'Boleto dupla']);

    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'ticket_type_id' => $type->id,
        'price' => 1800,
    ]);

    expect($ticket->fresh()->ticketType->id)->toBe($type->id)
        ->and($ticket->fresh()->ticketType->name)->toBe('Boleto dupla')
        ->and($type->tickets)->toHaveCount(1)
        ->and((float) $ticket->fresh()->price)->toBe(1800.0);
});

it('defaults a new ticket to the generated status', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->fresh()->status)->toBe(TicketStatus::Generated);
});

it('keeps a unique code per ticket', function () {
    $ticket = Ticket::factory()->create();

    expect(fn () => Ticket::factory()->create(['code' => $ticket->code]))
        ->toThrow(QueryException::class);
});

it('relates a ticket with its scans and the manager who scanned it', function () {
    $manager = User::factory()->create();
    $ticket = Ticket::factory()->create();

    Scan::factory()->create(['ticket_id' => $ticket->id, 'manager_id' => $manager->id, 'result' => ScanResult::Valid]);
    Scan::factory()->create(['ticket_id' => $ticket->id, 'manager_id' => $manager->id, 'result' => ScanResult::AlreadyUsed]);

    expect($ticket->scans)->toHaveCount(2)
        ->and($manager->scans)->toHaveCount(2)
        ->and($ticket->scans->first()->result)->toBe(ScanResult::Valid)
        ->and($ticket->scans->first()->manager->id)->toBe($manager->id);
});

it('describes ticket statuses and scan results in Spanish', function () {
    expect(TicketStatus::Generated->describe())->toBe('Generado')
        ->and(TicketStatus::Sent->describe())->toBe('Enviado')
        ->and(TicketStatus::Used->describe())->toBe('Usado')
        ->and(TicketStatus::Cancelled->describe())->toBe('Cancelado')
        ->and(ScanResult::Valid->describe())->toBe('Válido')
        ->and(ScanResult::Invalid->describe())->toBe('Inválido')
        ->and(ScanResult::AlreadyUsed->describe())->toBe('Ya usado');
});
