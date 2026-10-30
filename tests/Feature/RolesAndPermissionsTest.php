<?php

use App\Enums\UserRole;
use App\Helpers\PermissionMatrix;
use App\Http\Controllers\TicketController;
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
    'seller' => UserRole::Admin,
    'manager' => UserRole::Manager,
]);

it('lets only the admin manage events', function () {
    $event = Event::factory()->create();

    actingRole(UserRole::Admin);
    $this->get('/events')->assertOk();
    $this->get('/events/create')->assertOk();
    $this->get("/events/{$event->id}")->assertOk();
    $this->get("/events/{$event->id}/edit")->assertOk();

    actingRole(UserRole::Manager);
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
    $deletableUser = User::factory()->create();
    $this->delete("/users/{$deletableUser->id}")->assertRedirect(route('users.index'));

    actingRole(UserRole::Manager);
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

it('lets only the admin cancel a ticket', function () {
    $ticket = Ticket::factory()->create();

    actingRole(UserRole::Admin);
    $this->post("/tickets/{$ticket->id}/cancel")->assertRedirect(route('tickets.cancelled'));

    $other = Ticket::factory()->create();

    actingRole(UserRole::Manager);
    $this->post("/tickets/{$other->id}/cancel")->assertForbidden();
    $this->delete("/tickets/{$other->id}")->assertForbidden();

    actingRole(UserRole::Manager);
    $this->post("/tickets/{$other->id}/cancel")->assertForbidden();
});

it('lets the admin delete a ticket only when its user and client are associated', function () {
    $ticket = Ticket::factory()->create();

    actingRole(UserRole::Admin);
    $this->delete(route('tickets.destroy', $ticket))
        ->assertRedirect(route('tickets.cancelled'));

    expect($ticket->fresh()->isCancelled())->toBeTrue();
});

it('shows delete permissions for administrators in the permissions matrix', function () {
    $items = collect(PermissionMatrix::groups())
        ->flatMap(fn (array $group) => $group['items'])
        ->keyBy('label');

    expect($items['Eliminar usuarios sin ventas ni check-in']['admin'])->toBeTrue()
        ->and($items['Eliminar boletos y clientes con boleto asociado']['admin'])->toBeTrue()
        ->and($items['Eliminar usuarios sin ventas ni check-in']['manager'])->toBeFalse()
        ->and($items['Eliminar boletos y clientes con boleto asociado']['manager'])->toBeFalse();
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

it('lets only the admin see the cancelled tickets list', function () {
    actingRole(UserRole::Admin);
    $this->get('/tickets-cancelled')->assertOk();

    actingRole(UserRole::Manager);
    $this->get('/tickets-cancelled')->assertForbidden();

    actingRole(UserRole::Manager);
    $this->get('/tickets-cancelled')->assertForbidden();
});

it('exposes ticket edit, update, and cancellation routes', function () {
    $router = app('router');

    $edit = $router->getRoutes()->getByName('tickets.edit');
    expect($edit)->not->toBeNull()
        ->and($edit->uri())->toBe('tickets/{ticket}/edit')
        ->and($edit->methods())->toContain('GET')
        ->and($edit->getActionName())->toBe(TicketController::class.'@edit');

    $update = $router->getRoutes()->getByName('tickets.update');
    expect($update)->not->toBeNull()
        ->and($update->uri())->toBe('tickets/{ticket}')
        ->and($update->methods())->toBe(['PUT'])
        ->and($update->getActionName())->toBe(TicketController::class.'@update');

    $cancel = $router->getRoutes()->getByName('tickets.cancel');
    expect($cancel)->not->toBeNull()
        ->and($cancel->uri())->toBe('tickets/{ticket}/cancel')
        ->and($cancel->methods())->toContain('POST')
        ->and($cancel->getActionName())->toBe(TicketController::class.'@cancel');

    $show = $router->getRoutes()->getByName('tickets.show');
    expect($show)->not->toBeNull()
        ->and($show->uri())->toBe('tickets/{ticket}')
        ->and($show->methods())->toBe(['GET', 'HEAD'])
        ->and($show->getActionName())->toBe(TicketController::class.'@show');

    $routeRegistry = collect($router->getRoutes())
        ->mapWithKeys(fn ($route) => [$route->getName() => $route]);
    foreach (['tickets.edit', 'tickets.update', 'tickets.cancel'] as $name) {
        expect($routeRegistry->has($name))->toBeTrue();
    }
});
