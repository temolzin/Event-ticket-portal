<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function actingRole(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role]);

    test()->actingAs($user);

    return $user;
}

it('lets every role reach the dashboard', function (UserRole $role) {
    actingRole($role);

    $this->get('/')->assertOk();
})->with([
    'admin' => UserRole::Admin,
    'seller' => UserRole::Seller,
    'manager' => UserRole::Manager,
]);

it('lets only the admin manage events', function () {
    actingRole(UserRole::Admin);
    $this->get('/events')->assertOk();
    $this->get('/events/create')->assertOk();
    $this->get('/events/1')->assertOk();
    $this->get('/events/1/edit')->assertOk();

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

    actingRole(UserRole::Seller);
    $this->get('/users')->assertForbidden();

    actingRole(UserRole::Manager);
    $this->get('/users')->assertForbidden();
});

it('lets the admin and the seller manage tickets', function (UserRole $role) {
    actingRole($role);

    $this->get('/tickets')->assertOk();
    $this->get('/tickets/create')->assertOk();
    $this->get('/tickets/1')->assertOk();
    $this->get('/tickets/1/download')->assertRedirect(route('tickets.show', 1));
    $this->post('/tickets/1/send')->assertRedirect(route('tickets.show', 1));
})->with([
    'admin' => UserRole::Admin,
    'seller' => UserRole::Seller,
]);

it('blocks the manager from every ticket page', function () {
    actingRole(UserRole::Manager);

    $this->get('/tickets')->assertForbidden();
    $this->get('/tickets/create')->assertForbidden();
    $this->get('/tickets/1')->assertForbidden();
    $this->post('/tickets/1/send')->assertForbidden();
    $this->get('/tickets/1/download')->assertForbidden();
});

it('lets only the admin cancel a ticket', function () {
    actingRole(UserRole::Admin);
    $this->post('/tickets/1/cancel')->assertRedirect(route('tickets.show', 1));

    actingRole(UserRole::Seller);
    $this->post('/tickets/1/cancel')->assertForbidden();

    actingRole(UserRole::Manager);
    $this->post('/tickets/1/cancel')->assertForbidden();
});

it('lets the admin and the manager check in', function (UserRole $role) {
    actingRole($role);

    $this->get('/check-in')->assertOk();
    $this->get('/check-in/log')->assertOk();
    $this->post('/check-in')->assertOk();
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

it('never exposes an edit or delete route for tickets', function () {
    $ticketRoutes = collect(app('router')->getRoutes())
        ->map(fn ($route) => $route->uri())
        ->filter(fn (string $uri) => str_starts_with($uri, 'tickets'));

    expect($ticketRoutes->contains(fn (string $uri) => str_contains($uri, 'edit')))->toBeFalse()
        ->and($ticketRoutes->contains(fn (string $uri) => str_contains($uri, 'delete')))->toBeFalse()
        ->and($ticketRoutes->contains(fn (string $uri) => str_contains($uri, 'update')))->toBeFalse();
});
