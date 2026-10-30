<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('searches events, clients, and users for administrators', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $seller = User::factory()->create([
        'name' => 'Morgan Seller',
        'email' => 'morgan@example.test',
        'role' => UserRole::Admin,
    ]);
    $event = Event::factory()->create([
        'name' => 'Summer Conference',
        'venue' => 'North Hall',
    ]);
    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'seller_id' => $seller->id,
        'buyer_name' => 'Taylor Buyer',
        'buyer_email' => 'taylor@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('search', ['q' => 'Taylor']))
        ->assertOk()
        ->assertSee('Taylor Buyer')
        ->assertSee(route('tickets.show', $ticket));

    $this->actingAs($admin)
        ->get(route('search', ['q' => 'Summer']))
        ->assertOk()
        ->assertSee('Summer Conference')
        ->assertSee(route('events.show', $event));

    $this->actingAs($admin)
        ->get(route('search', ['q' => 'Morgan']))
        ->assertOk()
        ->assertSee('Morgan Seller')
        ->assertSee(route('users.edit', $seller));
});

it('validates search query length and shows the empty search prompt', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->get(route('search'))
        ->assertOk()
        ->assertSee('Escribe un término para buscar eventos, clientes o usuarios.');

    $this->actingAs($admin)
        ->get(route('search', ['q' => str_repeat('a', 101)]))
        ->assertSessionHasErrors('q');
});
