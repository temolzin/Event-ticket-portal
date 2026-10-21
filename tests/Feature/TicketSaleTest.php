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

function saleSeller(): User
{
    return User::factory()->create(['role' => UserRole::Seller]);
}

function saleEventWithType(array $event = [], array $type = []): array
{
    $event = Event::factory()->create($event + [
        'is_active' => true,
        'capacity' => 100,
        'early_quantity' => 30,
    ]);

    $ticketType = TicketType::factory()->create($type + [
        'event_id' => $event->id,
        'key' => 'individual',
        'name' => 'Boleto individual',
        'early_price' => 990,
        'regular_price' => 1500,
        'is_active' => true,
    ]);

    return [$event, $ticketType];
}

function salePayload(Event $event, TicketType $ticketType, array $overrides = []): array
{
    return $overrides + [
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
        'buyer_name' => 'Ana López',
        'buyer_email' => 'ana@ejemplo.com',
        'buyer_photo' => UploadedFile::fake()->image('cliente.jpg'),
        'payment_receipt' => UploadedFile::fake()->image('comprobante.jpg'),
    ];
}

it('registers a sale and stores the buyer data', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType();
    $seller = saleSeller();

    $this->actingAs($seller)
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertRedirect();

    $ticket = Ticket::query()->firstOrFail();

    expect($ticket->buyer_name)->toBe('Ana López')
        ->and($ticket->buyer_email)->toBe('ana@ejemplo.com')
        ->and($ticket->event_id)->toBe($event->id)
        ->and($ticket->ticket_type_id)->toBe($ticketType->id)
        ->and($ticket->seller_id)->toBe($seller->id)
        ->and($ticket->status)->toBe(TicketStatus::Generated)
        ->and($ticket->code)->not->toBeEmpty();
});

it('stores the buyer photo and the receipt on the private disk', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType();
    $seller = saleSeller();

    $this->actingAs($seller)
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertRedirect();

    $ticket = Ticket::query()->firstOrFail();

    expect($ticket->buyer_photo_path)->toStartWith('buyer-photos/')
        ->and($ticket->payment_receipt_path)->toStartWith('payment-receipts/');

    Storage::disk('local')->assertExists($ticket->buyer_photo_path);
    Storage::disk('local')->assertExists($ticket->payment_receipt_path);
});

it('charges the early price while the event is in the early phase', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType([], ['early_price' => 990, 'regular_price' => 1500]);

    $this->actingAs(saleSeller())
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertRedirect();

    expect((float) Ticket::query()->firstOrFail()->price)->toBe(990.0);
});

it('charges the regular price once the early seats are gone', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType(
        ['capacity' => 100, 'early_quantity' => 0],
        ['early_price' => 990, 'regular_price' => 1500]
    );

    $this->actingAs(saleSeller())
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertRedirect();

    expect((float) Ticket::query()->firstOrFail()->price)->toBe(1500.0);
});

it('charges the regular price for a type without an early price', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType([], ['early_price' => null, 'regular_price' => 700]);

    $this->actingAs(saleSeller())
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertRedirect();

    expect((float) Ticket::query()->firstOrFail()->price)->toBe(700.0);
});

it('rejects the sale when the event has no seats left', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType(['capacity' => 1, 'early_quantity' => 1]);

    Ticket::factory()->create([
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
    ]);

    $this->actingAs(saleSeller())
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertSessionHasErrors('event_id');

    expect(Ticket::query()->count())->toBe(1);
});

it('frees the seat when a ticket is cancelled', function () {
    [$event, $ticketType] = saleEventWithType(['capacity' => 1, 'early_quantity' => 1]);

    $full = Ticket::factory()->create([
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
    ]);

    expect($event->fresh()->countAvailableSeats())->toBe(0);

    $full->update(['status' => TicketStatus::Cancelled]);

    expect($event->fresh()->countAvailableSeats())->toBe(1);
});

it('rejects a sale without a buyer name, an email or the files', function () {
    [$event, $ticketType] = saleEventWithType();

    $this->actingAs(saleSeller())
        ->post('/tickets', [
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'buyer_name' => '',
            'buyer_email' => '',
        ])
        ->assertSessionHasErrors(['buyer_name', 'buyer_email', 'buyer_photo', 'payment_receipt']);
});

it('rejects a sale on an inactive event or an inactive type', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType(['is_active' => false]);

    $this->actingAs(saleSeller())
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertSessionHasErrors('event_id');

    [$activeEvent, $inactiveType] = saleEventWithType([], ['is_active' => false]);

    $this->actingAs(saleSeller())
        ->post('/tickets', salePayload($activeEvent, $inactiveType))
        ->assertSessionHasErrors('ticket_type_id');
});

it('lists only the sales of the seller who registered them', function () {
    $seller = saleSeller();
    $otherSeller = saleSeller();

    Ticket::factory()->create(['seller_id' => $seller->id, 'buyer_name' => 'Compra Propia']);
    Ticket::factory()->create(['seller_id' => $otherSeller->id, 'buyer_name' => 'Compra Ajena']);

    $this->actingAs($seller)
        ->get('/tickets')
        ->assertOk()
        ->assertSee('Compra Propia')
        ->assertDontSee('Compra Ajena');
});

it('shows every sale to the admin', function () {
    $seller = saleSeller();

    Ticket::factory()->create(['seller_id' => $seller->id, 'buyer_name' => 'Compra Del Vendedor']);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get('/tickets')
        ->assertOk()
        ->assertSee('Compra Del Vendedor');
});

it('groups the clients by event', function () {
    $seller = saleSeller();
    $firstEvent = Event::factory()->create(['name' => 'Evento Uno']);
    $secondEvent = Event::factory()->create(['name' => 'Evento Dos']);

    Ticket::factory()->create([
        'event_id' => $firstEvent->id,
        'seller_id' => $seller->id,
        'buyer_name' => 'Cliente Del Primero',
    ]);
    Ticket::factory()->create([
        'event_id' => $secondEvent->id,
        'seller_id' => $seller->id,
        'buyer_name' => 'Cliente Del Segundo',
    ]);

    $response = $this->actingAs($seller)->get('/tickets')->assertOk();

    $response->assertSee('Evento Uno')
        ->assertSee('Evento Dos')
        ->assertSee('Cliente Del Primero')
        ->assertSee('Cliente Del Segundo');
});

it('shows the count of clients per event', function () {
    $seller = saleSeller();
    $event = Event::factory()->create(['name' => 'Evento Con Tres']);

    Ticket::factory()->count(3)->create([
        'event_id' => $event->id,
        'seller_id' => $seller->id,
    ]);

    $this->actingAs($seller)
        ->get('/tickets')
        ->assertOk()
        ->assertSee('3 clientes registrados');
});

it('leaves out an event with no sales', function () {
    $seller = saleSeller();
    Event::factory()->create(['name' => 'Evento Sin Ventas']);

    $this->actingAs($seller)
        ->get('/tickets')
        ->assertOk()
        ->assertDontSee('Evento Sin Ventas');
});

it('keeps the seller out of a sale that belongs to someone else', function () {
    $ticket = Ticket::factory()->create();

    $this->actingAs(saleSeller())
        ->get("/tickets/{$ticket->id}")
        ->assertForbidden();
});

it('shows the ticket types of the event with the price of the phase', function () {
    [$event, $ticketType] = saleEventWithType();

    $response = $this->actingAs(saleSeller())
        ->get("/tickets/events/{$event->id}/types")
        ->assertOk();

    expect($response->json())->toHaveCount(1)
        ->and($response->json('0.id'))->toBe($ticketType->id)
        ->and($response->json('0.label'))->toBe('Boleto individual · $990.00');
});

it('keeps the manager out of the sale flow', function () {
    [$event, $ticketType] = saleEventWithType();

    $this->actingAs(User::factory()->create(['role' => UserRole::Manager]))
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertForbidden();
});
