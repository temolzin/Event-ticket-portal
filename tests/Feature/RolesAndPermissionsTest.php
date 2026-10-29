<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets every role reach the dashboard', function (UserRole $role) {
    actingRole($role);

    $this->get('/')->assertOk();
})->with([
    'admin' => UserRole::Admin,
    'seller' => UserRole::Seller,
    'manager' => UserRole::Manager,
]);

it('lets only the admin manage events', function () {
    $event = Event::factory()->create();

    actingRole(UserRole::Admin);
    $this->get('/events')->assertOk();
    $this->get('/events/create')->assertOk();
    $this->get("/events/{$event->id}")->assertOk();
    $this->get("/events/{$event->id}/edit")->assertOk();

    actingRole(UserRole::Seller);
    $this->get('/events')->assertForbidden();
    $this->get('/events/create')->assertForbidden();

    actingRole(UserRole::Manager);
    $this->get('/events')->assertForbidden();
    $this->get('/events/create')->assertForbidden();
});

it('lets only the admin manage users', function () {
    actingRole(UserRole::Admin);
    $this->get('/users')->assertOk();
    $this->get('/users/create')->assertOk();
    $user = User::factory()->create();
    $this->get("/users/{$user->id}/edit")->assertOk();

    actingRole(UserRole::Seller);
    $this->get('/users')->assertForbidden();
    $this->get('/users/create')->assertForbidden();
    $this->get("/users/{$user->id}/edit")->assertForbidden();
    $this->post('/users', [])->assertForbidden();
    $this->put("/users/{$user->id}", [])->assertForbidden();
    $this->delete("/users/{$user->id}")->assertForbidden();

    actingRole(UserRole::Manager);
    $this->get('/users')->assertForbidden();
    $this->get('/users/create')->assertForbidden();
    $this->get("/users/{$user->id}/edit")->assertForbidden();
    $this->post('/users', [])->assertForbidden();
    $this->put("/users/{$user->id}", [])->assertForbidden();
    $this->delete("/users/{$user->id}")->assertForbidden();
});

it('lets the admin and the seller manage tickets', function (UserRole $role) {
    $user = actingRole($role);
    $ticket = Ticket::factory()->create(['seller_id' => $user->id]);

    $this->get('/tickets')->assertOk();
    $this->get('/tickets/create')->assertOk();
    $this->get("/tickets/{$ticket->id}")->assertOk();
    $this->get("/tickets/{$ticket->id}/edit")->assertOk();
    $this->get(route('tickets.download', ['ticket' => $ticket->encryptedDownloadKey()]))->assertOk();
    $this->post("/tickets/{$ticket->id}/send")->assertRedirect(route('tickets.show', $ticket));
})->with([
    'admin' => UserRole::Admin,
    'seller' => UserRole::Seller,
]);

it('blocks the manager from every ticket page', function () {
    $ticket = Ticket::factory()->create();

    actingRole(UserRole::Manager);

    $this->get('/tickets')->assertForbidden();
    $this->get('/tickets/create')->assertForbidden();
    $this->get("/tickets/{$ticket->id}")->assertForbidden();
    $this->get("/tickets/{$ticket->id}/edit")->assertForbidden();
    $this->post("/tickets/{$ticket->id}/send")->assertForbidden();
    $this->get(route('tickets.download', ['ticket' => $ticket->encryptedDownloadKey()]))->assertForbidden();
});

it('lets only the admin cancel a ticket', function () {
    $ticket = Ticket::factory()->create();

    actingRole(UserRole::Admin);
    $this->post("/tickets/{$ticket->id}/cancel")->assertRedirect(route('tickets.cancelled'));

    $other = Ticket::factory()->create();

    actingRole(UserRole::Seller);
    $this->post("/tickets/{$other->id}/cancel")->assertForbidden();
    $this->delete("/tickets/{$other->id}")->assertForbidden();

    actingRole(UserRole::Manager);
    $this->post("/tickets/{$other->id}/cancel")->assertForbidden();
});

it('lets the admin and the manager check in', function (UserRole $role) {
    actingRole($role);

    $ticket = Ticket::factory()->create();

    $this->get('/check-in')->assertOk();
    $this->get('/check-in/log')->assertOk();
    $this->post('/check-in', ['code' => $ticket->code])->assertRedirect();
})->with([
    'admin' => UserRole::Admin,
    'manager' => UserRole::Manager,
]);

it('blocks the seller from the check-in pages', function () {
    actingRole(UserRole::Seller);

    $this->get('/check-in')->assertForbidden();
    $this->get('/check-in/log')->assertForbidden();
    $this->post('/check-in')->assertForbidden();
});

it('lets only the admin see the cancelled tickets list', function () {
    actingRole(UserRole::Admin);
    $this->get('/tickets-cancelled')->assertOk();

    actingRole(UserRole::Seller);
    $this->get('/tickets-cancelled')->assertForbidden();

    actingRole(UserRole::Manager);
    $this->get('/tickets-cancelled')->assertForbidden();
});

it('exposes ticket edit, update, and cancellation routes', function () {
    $ticketRoutes = collect(app('router')->getRoutes())
        ->map(fn ($route) => $route->uri())
        ->filter(fn (string $uri) => str_starts_with($uri, 'tickets'));

    expect($ticketRoutes->contains(fn (string $uri) => str_contains($uri, 'edit')))->toBeTrue()
        ->and($ticketRoutes->contains(fn (string $uri) => str_contains($uri, '{ticket}')))->toBeTrue();
});
