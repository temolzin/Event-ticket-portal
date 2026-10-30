<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';

    public function describe(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
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
