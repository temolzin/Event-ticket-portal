<?php

namespace App\Enums;

enum ScanResult: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';
    case NotFound = 'not_found';
    case Cancelled = 'cancelled';
    case AlreadyUsed = 'already_used';

    public function describe(): string
    {
        return match ($this) {
            self::Valid => 'Válido',
            self::Invalid => 'Inválido',
            self::NotFound => 'No encontrado',
            self::Cancelled => 'Cancelado',
            self::AlreadyUsed => 'Ya usado',
        };
    }

    public function reason(): string
    {
        return match ($this) {
            self::Valid => 'El boleto es válido. Acceso permitido.',
            self::Invalid => 'El código capturado no es válido.',
            self::NotFound => 'El boleto no existe. Revisa el código capturado.',
            self::Cancelled => 'El boleto fue cancelado y no permite el acceso.',
            self::AlreadyUsed => 'El boleto ya fue usado en una entrada anterior.',
        };
    }

    public function isAllowed(): bool
    {
        return $this === self::Valid;
    }
}
