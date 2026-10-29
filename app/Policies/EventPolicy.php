<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ManageEvents);
    }

    public function view(User $user, Event $event): bool
    {
        return $user->hasPermission(Permission::ManageEvents);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageEvents);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->hasPermission(Permission::ManageEvents);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->hasPermission(Permission::ManageEvents) && $event->canBeDeleted();
    }
}
