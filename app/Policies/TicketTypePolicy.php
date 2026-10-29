<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\TicketType;
use App\Models\User;

class TicketTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ManageTicketTypes);
    }

    public function view(User $user, TicketType $ticketType): bool
    {
        return $user->hasPermission(Permission::ManageTicketTypes);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageTicketTypes);
    }

    public function update(User $user, TicketType $ticketType): bool
    {
        return $user->hasPermission(Permission::ManageTicketTypes);
    }

    public function delete(User $user, TicketType $ticketType): bool
    {
        return $user->hasPermission(Permission::ManageTicketTypes) && ! $ticketType->hasSales();
    }
}
