<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('shows the profile and password forms in Spanish', function () {
    $user = User::factory()->create(['name' => 'Ana Pérez']);

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('Mi perfil')
        ->assertSee('Nombre')
        ->assertSee('Cambiar contraseña')
        ->assertSee('Contraseña actual')
        ->assertDontSee('Address')
        ->assertDontSee('Danger Zone');
});

it('updates the authenticated user name', function () {
    $user = User::factory()->create(['name' => 'Nombre anterior']);

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'Nombre nuevo'])
        ->assertRedirect(route('profile'))
        ->assertSessionHas('status', 'Tu nombre se actualizó correctamente.');

    expect($user->fresh()->name)->toBe('Nombre nuevo');
});

it('requires a valid current password and confirmation to update password', function () {
    $user = User::factory()->create(['password' => 'current-password']);

    $this->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'incorrect-password',
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])
        ->assertSessionHasErrors('current_password');

    $this->put(route('profile.password.update'), [
        'current_password' => 'current-password',
        'password' => 'replacement-password',
        'password_confirmation' => 'different-password',
    ])->assertSessionHasErrors('password');

    $this->put(route('profile.password.update'), [
        'current_password' => 'current-password',
        'password' => 'replacement-password',
        'password_confirmation' => 'replacement-password',
    ])
        ->assertRedirect(route('profile'))
        ->assertSessionHas('status', 'Tu contraseña se actualizó correctamente.');

    expect(Hash::check('replacement-password', $user->fresh()->password))->toBeTrue();
});
