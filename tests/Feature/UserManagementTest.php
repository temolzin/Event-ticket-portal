<?php

use App\Enums\ScanResult;
use App\Enums\UserRole;
use App\Models\Scan;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createUserManagementAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

function buildValidUserPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Taylor Seller',
        'email' => 'taylor@example.test',
        'role' => UserRole::Seller->value,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

it('lists users and filters by name, email, and role', function () {
    $seller = User::factory()->create([
        'name' => 'Taylor Seller',
        'email' => 'taylor@example.test',
        'role' => UserRole::Seller,
    ]);
    User::factory()->create([
        'name' => 'Morgan Manager',
        'email' => 'morgan@example.test',
        'role' => UserRole::Manager,
    ]);

    $this->actingAs(createUserManagementAdmin())
        ->get('/users?search=taylor&role=seller')
        ->assertOk()
        ->assertSee('Taylor Seller')
        ->assertDontSee('Morgan Manager');

    $this->actingAs(createUserManagementAdmin())
        ->get('/users?search=example.test')
        ->assertOk()
        ->assertSee('Taylor Seller')
        ->assertSee('Morgan Manager');
});

it('shows the create and edit forms', function () {
    $admin = createUserManagementAdmin();
    $user = User::factory()->create(['name' => 'Jordan Seller']);

    $this->actingAs($admin)
        ->get('/users/create')
        ->assertOk()
        ->assertSee('Crear usuario')
        ->assertSee('Confirmar contraseña');

    $this->actingAs($admin)
        ->get(route('users.edit', $user))
        ->assertOk()
        ->assertSee('Jordan Seller')
        ->assertSee('Déjalo vacío para conservar la contraseña actual');
});

it('creates a user with a hashed password and selected role', function () {
    $this->actingAs(createUserManagementAdmin())
        ->post('/users', buildValidUserPayload(['role' => UserRole::Manager->value]))
        ->assertRedirect(route('users.index'));

    $user = User::query()->where('email', 'taylor@example.test')->firstOrFail();

    expect($user->name)->toBe('Taylor Seller')
        ->and($user->role)->toBe(UserRole::Manager)
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

it('validates required fields, unique emails, roles, and password confirmation', function () {
    $existing = User::factory()->create(['email' => 'used@example.test']);

    $this->actingAs(createUserManagementAdmin())
        ->post('/users', [])
        ->assertSessionHasErrors(['name', 'email', 'role', 'password']);

    $this->post('/users', buildValidUserPayload([
        'email' => $existing->email,
        'role' => 'unknown',
        'password_confirmation' => 'different-password',
    ]))
        ->assertSessionHasErrors(['email', 'role', 'password']);

    expect(User::query()->count())->toBe(2);
});

it('updates user details and keeps the password when none is provided', function () {
    $user = User::factory()->create([
        'name' => 'Before',
        'email' => 'before@example.test',
        'password' => 'current-password',
        'role' => UserRole::Seller,
    ]);
    $currentPassword = $user->password;

    $this->actingAs(createUserManagementAdmin())
        ->put(route('users.update', $user), [
            'name' => 'After',
            'email' => $user->email,
            'role' => UserRole::Manager->value,
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('users.index'));

    $user->refresh();

    expect($user->name)->toBe('After')
        ->and($user->role)->toBe(UserRole::Manager)
        ->and($user->password)->toBe($currentPassword);
});

it('updates the password when a confirmed replacement is provided', function () {
    $user = User::factory()->create();

    $this->actingAs(createUserManagementAdmin())
        ->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'password' => 'replacement123',
            'password_confirmation' => 'replacement123',
        ])
        ->assertRedirect(route('users.index'));

    expect(Hash::check('replacement123', $user->fresh()->password))->toBeTrue();
});

it('allows deleting a user without sales or check-in history', function () {
    $user = User::factory()->create();

    $this->actingAs(createUserManagementAdmin())
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    expect(User::query()->whereKey($user->id)->exists())->toBeFalse();
});

it('prevents deleting users with sales or check-in history', function () {
    $seller = User::factory()->create(['role' => UserRole::Seller]);
    Ticket::factory()->create(['seller_id' => $seller->id]);

    $manager = User::factory()->create(['role' => UserRole::Manager]);
    Scan::factory()->create([
        'manager_id' => $manager->id,
        'result' => ScanResult::NotFound,
    ]);

    $admin = createUserManagementAdmin();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $seller))
        ->assertSessionHasErrors('user');

    $this->delete(route('users.destroy', $manager))
        ->assertSessionHasErrors('user');

    expect(User::query()->whereKey($seller->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($manager->id)->exists())->toBeTrue()
        ->and(Ticket::query()->where('seller_id', $seller->id)->exists())->toBeTrue()
        ->and(Scan::query()->where('manager_id', $manager->id)->exists())->toBeTrue();
});

it('prevents deleting yourself or the last administrator', function () {
    $admin = createUserManagementAdmin();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertSessionHasErrors('user');

    $otherUser = User::factory()->create();

    $this->delete(route('users.destroy', $admin))
        ->assertSessionHasErrors('user');

    $this->put(route('users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => UserRole::Seller->value,
        'password' => '',
        'password_confirmation' => '',
    ])->assertSessionHasErrors('role');

    expect(User::query()->whereKey($admin->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($otherUser->id)->exists())->toBeTrue();
});

it('prevents demoting the last administrator or removing the current admin role', function () {
    $admin = createUserManagementAdmin();
    $staff = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($admin)
        ->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => UserRole::Seller->value,
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertSessionHasErrors('role');

    $otherAdmin = createUserManagementAdmin();

    $this->put(route('users.update', $otherAdmin), [
        'name' => $otherAdmin->name,
        'email' => $otherAdmin->email,
        'role' => UserRole::Manager->value,
        'password' => '',
        'password_confirmation' => '',
    ])
        ->assertRedirect(route('users.index'));

    $this->put(route('users.update', $staff), [
        'name' => $staff->name,
        'email' => $staff->email,
        'role' => UserRole::Seller->value,
        'password' => '',
        'password_confirmation' => '',
    ])
        ->assertRedirect(route('users.index'));
});
