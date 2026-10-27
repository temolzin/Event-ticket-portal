<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventLeadController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.welcome');
})->name('home');

Route::post('/event-leads', [EventLeadController::class, 'store'])->name('event-leads.store');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->middleware('auth')
    ->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('/signin', [AuthenticatedSessionController::class, 'create'])->name('signin');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::patch('/profile', [ProfileController::class, 'updateName'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');

    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Perfil']);
    })->name('profile');

    Route::middleware('role:admin')->group(function () {
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        Route::get('/events/{event}/prices', [TicketTypeController::class, 'index'])->name('events.prices');
        Route::post('/ticket-types', [TicketTypeController::class, 'store'])->name('ticket-types.store');
        Route::patch('/events/{event}/prices/{ticketType}', [TicketTypeController::class, 'toggle'])
            ->name('ticket-types.toggle');
        Route::delete('/events/{event}/prices/{ticketType}', [TicketTypeController::class, 'destroy'])
            ->name('ticket-types.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('role:admin,seller')->group(function () {
        Route::get('/badges', [BadgeController::class, 'index'])->name('badges.index');
        Route::get('/badges/events/{event}', [BadgeController::class, 'showEvent'])->name('badges.events.show');
        Route::get('/badges/events/{event}/print', [BadgeController::class, 'printAll'])->name('badges.events.print');
        Route::post('/badges/events/{event}/print-selected', [BadgeController::class, 'printSelected'])
            ->name('badges.events.print-selected');
        Route::get('/badges/tickets/{ticket}', [BadgeController::class, 'printTicket'])
            ->name('badges.tickets.print');

        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::get('/tickets/cancelled-match', [TicketController::class, 'checkCancelledClient'])
            ->name('tickets.cancelled-match');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/events/{event}/types', [TicketController::class, 'eventTypes'])->name('tickets.event-types');
        Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::get('/tickets/{ticket}/photo', [TicketController::class, 'buyerPhoto'])->name('tickets.photo');
        Route::get('/tickets/{ticket}/photo/download', [TicketController::class, 'buyerPhotoDownload'])->name('tickets.photo-download');
        Route::get('/tickets/{ticket}/receipt', [TicketController::class, 'paymentReceipt'])->name('tickets.receipt');
        Route::post('/tickets/{ticket}/send', [TicketController::class, 'send'])->name('tickets.send');
        Route::get('/tickets/{ticket}/download', [TicketController::class, 'download'])->name('tickets.download');
        Route::post('/tickets/{ticket}/cancel', [TicketController::class, 'cancel'])
            ->middleware('role:admin')
            ->name('tickets.cancel');
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('tickets.destroy');

        Route::get('/tickets-cancelled', [TicketController::class, 'cancelled'])
            ->middleware('role:admin')
            ->name('tickets.cancelled');
    });

    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/check-in', [CheckInController::class, 'create'])->name('check-in.scan');
        Route::post('/check-in', [CheckInController::class, 'store'])->name('check-in.store');
        Route::get('/check-in/{scan}/result', [CheckInController::class, 'result'])->name('check-in.result');
        Route::get('/check-in/log', [CheckInController::class, 'index'])->name('check-in.log');
    });
});
