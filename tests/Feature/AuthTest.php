<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

function sellerUser(): User
{
    return User::factory()->create(['role' => UserRole::Seller]);
}

function managerUser(): User
{
    return User::factory()->create(['role' => UserRole::Manager]);
}

it('redirects guests to the sign-in page', function () {
    $this->get('/')->assertRedirect(route('signin'));
});

it('blocks guests from every protected page', function () {
    $this->get('/events')->assertRedirect(route('signin'));
    $this->get('/tickets')->assertRedirect(route('signin'));
    $this->get('/check-in')->assertRedirect(route('signin'));
    $this->get('/users')->assertRedirect(route('signin'));
});

it('shows the sign-in page to guests', function () {
    $this->get('/signin')
        ->assertOk()
        ->assertSee('Portal de Eventos')
        ->assertSeeInOrder(['Portal de Eventos', 'Iniciar sesión'])
        ->assertDontSee('TailAdmin')
        ->assertDontSee('Free and Open-Source Tailwind CSS Admin Dashboard Template')
        ->assertDontSee('/images/shape/grid-01.svg');
});

it('redirects authenticated users away from the sign-in page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/signin')->assertRedirect(route('dashboard'));
});

it('rejects invalid credentials', function () {
    $this->from(route('signin'))
        ->post('/login', ['email' => 'nobody@eventportal.test', 'password' => 'wrong'])
        ->assertRedirect(route('signin'));

    $this->assertGuest();
});

it('authenticates with valid credentials and logs out', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $this->get('/')->assertOk();

    $this->post('/logout')->assertRedirect(route('signin'));

    $this->assertGuest();
});

it('applies the role middleware to block unauthorized roles', function () {
    Route::middleware(['auth', 'role:admin'])->get('/__role-check', fn () => 'allowed');

    $seller = User::factory()->create(['role' => UserRole::Seller]);
    $this->actingAs($seller)->get('/__role-check')->assertForbidden();

    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $this->actingAs($manager)->get('/__role-check')->assertForbidden();

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($admin)->get('/__role-check')->assertOk();
});
