<?php

namespace App\Enums;

use App\Enums\Permission;

enum UserRole: string
{
    case Admin = 'admin';
    case Seller = 'seller';
    case Manager = 'manager';

    public function describe(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Seller => 'Vendedor',
            self::Manager => 'Gestor',
        };
    }

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Seller => [
                Permission::ViewDashboard,
                Permission::ViewTickets,
                Permission::CreateTicket,
                Permission::UpdateOwnTicket,
                Permission::ManageBadges,
                Permission::ViewProfile,
                Permission::Search,
            ],
            self::Manager => [
                Permission::ViewDashboard,
                Permission::PerformCheckIn,
                Permission::ViewScanLog,
                Permission::ViewProfile,
                Permission::Search,
            ],
        };
    }

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
