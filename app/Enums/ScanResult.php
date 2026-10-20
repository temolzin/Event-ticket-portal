<?php

namespace App\Enums;

enum ScanResult: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';
    case AlreadyUsed = 'already_used';

    public function describe(): string
    {
        return match ($this) {
            self::Valid => 'Válido',
            self::Invalid => 'Inválido',
            self::AlreadyUsed => 'Ya usado',
        };
    }
}
