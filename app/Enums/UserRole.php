<?php

namespace App\Enums;

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
}
