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

it('rejects a duo ticket when only one seat remains', function () {
    Storage::fake('local');

    [$event, $ticketType] = saleEventWithType(['capacity' => 1, 'early_quantity' => 1], [
        'key' => 'duo',
        'name' => 'Boleto dupla',
        'seats_per_ticket' => 2,
    ]);

    $this->actingAs(saleSeller())
        ->post('/tickets', salePayload($event, $ticketType))
        ->assertSessionHasErrors('event_id');

    expect(Ticket::query()->count())->toBe(0);
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

it('allows the seller to edit their customer details without changing ticket data', function () {
    Storage::fake('local');
    $seller = saleSeller();
    $ticket = Ticket::factory()->create([
        'seller_id' => $seller->id,
        'buyer_name' => 'Ana López',
        'buyer_email' => 'ana@ejemplo.com',
        'status' => TicketStatus::Sent,
        'sent_at' => now(),
    ]);

    $this->actingAs($seller)
        ->get(route('tickets.edit', $ticket))
        ->assertOk()
        ->assertSee('Ana López')
        ->assertSee('Editar cliente')
        ->assertSee('href="'.route('tickets.index').'"', false)
        ->assertSee('href="'.route('tickets.show', $ticket).'"', false);

    $this->put(route('tickets.update', $ticket), [
        'buyer_name' => 'Andrea López',
        'buyer_email' => 'ANDREA@EJEMPLO.COM',
    ])
        ->assertRedirect(route('tickets.show', $ticket));

    expect($ticket->fresh()->buyer_name)->toBe('Andrea López')
        ->and($ticket->fresh()->buyer_email)->toBe('andrea@ejemplo.com')
        ->and($ticket->fresh()->status)->toBe(TicketStatus::Sent)
        ->and($ticket->fresh()->code)->toBe($ticket->code);
});

it('warns when a customer name or email matches a cancelled client', function () {
    $seller = saleSeller();
    Ticket::factory()->create([
        'status' => TicketStatus::Cancelled,
        'buyer_name' => 'Ana López',
        'buyer_email' => 'ana@example.com',
    ]);
    Ticket::factory()->create([
        'status' => TicketStatus::Sent,
        'buyer_name' => 'Active Client',
        'buyer_email' => 'active@example.com',
    ]);

    $this->actingAs($seller)
        ->getJson(route('tickets.cancelled-match', [
            'buyer_name' => '  ANA López ',
            'buyer_email' => 'different@example.com',
        ]))
        ->assertOk()
        ->assertJson(['exists' => true]);

    $this->getJson(route('tickets.cancelled-match', [
        'buyer_name' => 'Another Name',
        'buyer_email' => 'ANA@example.com',
    ]))
        ->assertOk()
        ->assertJson(['exists' => true]);

    $this->getJson(route('tickets.cancelled-match', [
        'buyer_name' => 'Active Client',
        'buyer_email' => 'active@example.com',
    ]))
        ->assertOk()
        ->assertJson(['exists' => false]);
});

it('requires confirmation before registering a customer from the cancelled list', function () {
    Storage::fake('local');
    [$event, $ticketType] = saleEventWithType();
    $seller = saleSeller();
    Ticket::factory()->create([
        'status' => TicketStatus::Cancelled,
        'event_id' => $event->id,
        'buyer_name' => 'Ana López',
        'buyer_email' => 'ana@ejemplo.com',
    ]);
    $payload = salePayload($event, $ticketType);

    $this->actingAs($seller)
        ->post(route('tickets.store'), $payload)
        ->assertSessionHasErrors('cancelled_client_confirmation');

    expect(Ticket::query()->count())->toBe(1);

    $this->post(route('tickets.store'), $payload + ['confirm_cancelled_client' => '1'])
        ->assertRedirect();

    expect(Ticket::query()->count())->toBe(2);
});

it('replaces optional customer files and removes the previous private files', function () {
    Storage::fake('local');
    $seller = saleSeller();
    $ticket = Ticket::factory()->create(['seller_id' => $seller->id]);
    $ticket->storeBuyerPhoto(UploadedFile::fake()->image('old-photo.jpg'));
    $ticket->storePaymentReceipt(UploadedFile::fake()->image('old-receipt.jpg'));
    $ticket->save();
    $oldPhoto = $ticket->buyer_photo_path;
    $oldReceipt = $ticket->payment_receipt_path;

    $this->actingAs($seller)
        ->put(route('tickets.update', $ticket), [
            'buyer_name' => 'Cliente Actualizado',
            'buyer_email' => 'actualizado@ejemplo.com',
            'buyer_photo' => UploadedFile::fake()->image('new-photo.jpg'),
            'payment_receipt' => UploadedFile::fake()->image('new-receipt.jpg'),
        ])
        ->assertRedirect(route('tickets.show', $ticket));

    $updated = $ticket->fresh();
    expect($updated->buyer_photo_path)->not->toBe($oldPhoto)
        ->and($updated->payment_receipt_path)->not->toBe($oldReceipt);

    Storage::disk('local')->assertMissing($oldPhoto);
    Storage::disk('local')->assertMissing($oldReceipt);
    Storage::disk('local')->assertExists($updated->buyer_photo_path);
    Storage::disk('local')->assertExists($updated->payment_receipt_path);
});

it('prevents sellers from editing another sellers customer and blocks used or cancelled tickets', function () {
    $seller = saleSeller();
    $otherSellerTicket = Ticket::factory()->create(['seller_id' => saleSeller()->id]);
    $usedTicket = Ticket::factory()->create([
        'seller_id' => $seller->id,
        'status' => TicketStatus::Used,
        'checked_in_at' => now(),
    ]);
    $cancelledTicket = Ticket::factory()->create([
        'seller_id' => $seller->id,
        'status' => TicketStatus::Cancelled,
    ]);

    $this->actingAs($seller)
        ->get(route('tickets.edit', $otherSellerTicket))
        ->assertForbidden();

    $this->get(route('tickets.edit', $usedTicket))->assertForbidden();
    $this->get(route('tickets.edit', $cancelledTicket))->assertForbidden();
});

it('moves a cancelled customer out of the active module and into cancelled clients', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $activeTicket = Ticket::factory()->create(['buyer_name' => 'Active Client']);
    $cancelledTicket = Ticket::factory()->create(['buyer_name' => 'Cancelled Client']);

    $this->actingAs($admin)
        ->delete(route('tickets.destroy', $cancelledTicket))
        ->assertRedirect(route('tickets.cancelled'));

    $this->get(route('tickets.index'))
        ->assertOk()
        ->assertSee('Active Client')
        ->assertDontSee('Cancelled Client');

    $this->get(route('tickets.cancelled'))
        ->assertOk()
        ->assertSee('Cancelled Client');

    expect($cancelledTicket->fresh()->status)->toBe(TicketStatus::Cancelled)
        ->and(Ticket::query()->whereKey($activeTicket->id)->exists())->toBeTrue();
});

it('groups cancelled tickets by event', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $firstEvent = Event::factory()->create(['name' => 'Evento Cancelado Uno']);
    $secondEvent = Event::factory()->create(['name' => 'Evento Cancelado Dos']);
    $firstTicket = Ticket::factory()->create([
        'event_id' => $firstEvent->id,
        'status' => TicketStatus::Cancelled,
        'buyer_name' => 'Cliente Uno',
    ]);
    $secondTicket = Ticket::factory()->create([
        'event_id' => $secondEvent->id,
        'status' => TicketStatus::Cancelled,
        'buyer_name' => 'Cliente Dos',
    ]);

    $this->actingAs($admin)
        ->get(route('tickets.cancelled'))
        ->assertOk()
        ->assertSeeInOrder([
            'Evento Cancelado Uno',
            $firstTicket->buyer_name,
            'Evento Cancelado Dos',
            $secondTicket->buyer_name,
        ]);
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
