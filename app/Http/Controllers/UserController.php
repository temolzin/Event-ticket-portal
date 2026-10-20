<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController
{
    public function index(): View
    {
        return view('pages.users.index', ['title' => 'Usuarios']);
    }

    public function create(): View
    {
        return view('pages.users.create', ['title' => 'Nuevo usuario']);
    }

    public function edit(int $user): View
    {
        return view('pages.users.edit', ['title' => 'Editar usuario', 'user' => $user]);
    }

    public function store(): RedirectResponse
    {
        return to_route('users.index');
    }

    public function update(int $user): RedirectResponse
    {
        return to_route('users.index');
    }
}
