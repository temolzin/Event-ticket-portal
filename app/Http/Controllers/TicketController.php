<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController
{
    public function index(): View
    {
        return view('pages.tickets.index', ['title' => 'Boletos']);
    }

    public function create(): View
    {
        return view('pages.tickets.create', ['title' => 'Nuevo boleto']);
    }

    public function show(int $ticket): View
    {
        return view('pages.tickets.show', ['title' => 'Detalle del boleto', 'ticket' => $ticket]);
    }

    public function store(): RedirectResponse
    {
        return to_route('tickets.index');
    }

    public function send(int $ticket): RedirectResponse
    {
        return to_route('tickets.show', $ticket);
    }

    public function download(int $ticket): RedirectResponse
    {
        return to_route('tickets.show', $ticket);
    }

    public function cancel(Request $request, int $ticket): RedirectResponse
    {
        return to_route('tickets.show', $ticket);
    }

    public function cancelled(): View
    {
        return view('pages.tickets.cancelled', ['title' => 'Boletos cancelados']);
    }
}
