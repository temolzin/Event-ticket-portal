<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('rejects whitespace-only credentials at login', function () {
    $this->from('/login')
        ->post('/login', [
            'email' => '   ',
            'password' => '        ',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['email', 'password']);

    $this->assertGuest();
});

it('rejects whitespace-only user attributes', function () {
    actingRole(UserRole::Admin);
    $this->from('/users/create')
        ->post('/users', [
            'name' => '   ',
            'email' => '  ',
            'role' => UserRole::Admin->value,
            'password' => '        ',
            'password_confirmation' => '        ',
        ])
        ->assertSessionHasErrors(['name', 'email', 'password']);

    expect(User::query()->where('name', '   ')->exists())->toBeFalse();
});

it('rejects whitespace-only user email on update', function () {
    $admin = actingRole(UserRole::Admin);
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->from("/users/{$user->id}/edit")
        ->put("/users/{$user->id}", [
            'name' => 'Valid Name',
            'email' => '   ',
            'role' => UserRole::Admin->value,
        ])
        ->assertSessionHasErrors('email');

    expect($user->fresh()->email)->not->toBe('');
});

it('rejects whitespace-only event attributes including nested ticket types', function () {
    actingRole(UserRole::Admin);
    $this->from('/events/create')
        ->post('/events', [
            'name' => '  ',
            'venue' => '  ',
            'starts_at' => '  ',
            'capacity' => '  ',
            'early_quantity' => '  ',
            'is_active' => '1',
            'ticket_types' => [
                ['name' => '  ', 'regular_price' => '  ', 'seats_per_ticket' => '  ', 'early_price' => '  '],
            ],
        ])
        ->assertSessionHasErrors([
            'name',
            'venue',
            'starts_at',
            'capacity',
            'early_quantity',
            'ticket_types.0.name',
            'ticket_types.0.regular_price',
        ]);

    expect(Event::query()->count())->toBe(0);
});

it('rejects whitespace-only ticket type names with a localized message', function () {
    $event = Event::factory()->create();

    actingRole(UserRole::Admin);
    $this->from('/events/'.$event->id)
        ->post('/ticket-types', [
            'name' => '  ',
            'regular_price' => '  ',
            'event_id' => $event->id,
        ])
        ->assertSessionHasErrors(['name', 'regular_price', 'key']);

    expect(session('errors')->getBag('default')->first('key'))
        ->toBe('Escribe el nombre del boleto.');
});

it('rejects whitespace-only buyer attributes at ticket sale', function () {
    $seller = User::factory()->create(['role' => UserRole::Admin]);
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);

    $this->actingAs($seller)
        ->from('/tickets/create')
        ->post('/tickets', [
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'buyer_name' => '  ',
            'buyer_email' => '  ',
            'buyer_photo' => UploadedFile::fake()->image('buyer.jpg'),
            'payment_receipt' => UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['buyer_name', 'buyer_email']);
});

it('rejects whitespace-only event lead submissions', function () {
    $this->postJson('/event-leads', [
        'name' => '  ',
        'company' => '  ',
        'email' => '   ',
        'phone' => '  ',
        'ticket_type' => '  ',
    ])->assertJsonValidationErrors(['name', 'company', 'email', 'phone', 'ticket_type']);

    expect(\App\Models\EventLead::query()->count())->toBe(0);
});

it('rejects whitespace-only check-in codes and trims padded real codes', function () {
    $manager = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($manager)
        ->post('/check-in', ['code' => '  '])
        ->assertSessionHasErrors('code');

    $this->actingAs($manager)
        ->post('/check-in', ['code' => ' '.Str::random(30).' '])
        ->assertSessionHasNoErrors();
});
