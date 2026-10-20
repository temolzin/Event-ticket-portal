<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController
{
    public function index(): View
    {
        return view('pages.events.index', ['title' => 'Eventos']);
    }

    public function create(): View
    {
        return view('pages.events.create', ['title' => 'Nuevo evento']);
    }

    public function show(int $event): View
    {
        return view('pages.events.show', ['title' => 'Detalle del evento', 'event' => $event]);
    }

    public function edit(int $event): View
    {
        return view('pages.events.edit', ['title' => 'Editar evento', 'event' => $event]);
    }

    public function store(): RedirectResponse
    {
        return to_route('events.index');
    }

    public function update(int $event): RedirectResponse
    {
        return to_route('events.show', $event);
    }
}
