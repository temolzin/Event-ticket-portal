<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewTickets);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission(Permission::ViewTickets)
            && ($user->isAdmin() || $user->id === $ticket->seller_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateTicket);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission(Permission::UpdateOwnTicket)
            && ($user->isAdmin() || $user->id === $ticket->seller_id)
            && ! $ticket->isCancelled()
            && ! $ticket->wasUsed();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function send(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission(Permission::UpdateOwnTicket)
            && ($user->isAdmin() || $user->id === $ticket->seller_id)
            && ! $ticket->isCancelled()
            && ! $ticket->wasUsed();
    }

    public function cancel(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission(Permission::CancelTicket)
            && ! $ticket->isCancelled()
            && ! $ticket->wasUsed();
    }

    public function viewCancelled(User $user): bool
    {
        return $user->hasPermission(Permission::ViewCancelledTickets);
    }

    public function checkIn(User $user): bool
    {
        return $user->hasPermission(Permission::PerformCheckIn);
    }
}
