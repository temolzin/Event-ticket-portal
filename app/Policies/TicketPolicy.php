<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->role === UserRole::Seller;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->id === $ticket->seller_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->role === UserRole::Seller;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function send(User $user, Ticket $ticket): bool
    {
        return ($user->isAdmin() || $user->id === $ticket->seller_id)
            && ! $ticket->isCancelled()
            && ! $ticket->wasUsed();
    }

    public function cancel(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() && ! $ticket->isCancelled() && ! $ticket->wasUsed();
    }

    public function viewCancelled(User $user): bool
    {
        return $user->isAdmin();
    }

    public function checkIn(User $user): bool
    {
        return $user->isAdmin() || $user->role === UserRole::Manager;
    }
}
