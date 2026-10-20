<?php

use App\Enums\ScanResult;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Scan;
use App\Models\Ticket;
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
        'price' => 150.00,
        'is_active' => false,
    ]);

    $stored = Event::find($event->id);

    expect($stored->name)->toBe('Rock Night')
        ->and($stored->description)->toBe('Concierto de rock')
        ->and($stored->venue)->toBe('Auditorio Municipal')
        ->and($stored->starts_at->format('Y-m-d H:i'))->toBe('2026-12-31 20:00')
        ->and($stored->capacity)->toBe(100)
        ->and((float) $stored->price)->toBe(150.0)
        ->and($stored->is_active)->toBeFalse();
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
