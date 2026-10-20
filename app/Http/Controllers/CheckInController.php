<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInController
{
    public function create(): View
    {
        return view('pages.check-in.scan', ['title' => 'Check-in']);
    }

    public function store(Request $request): View
    {
        return view('pages.check-in.scan', ['title' => 'Check-in']);
    }

    public function index(): View
    {
        return view('pages.check-in.log', ['title' => 'Bitácora de escaneos']);
    }
}
