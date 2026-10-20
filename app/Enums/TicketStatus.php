<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Generated = 'generated';
    case Sent = 'sent';
    case Used = 'used';
    case Cancelled = 'cancelled';

    public function describe(): string
    {
        return match ($this) {
            self::Generated => 'Generado',
            self::Sent => 'Enviado',
            self::Used => 'Usado',
            self::Cancelled => 'Cancelado',
        };
    }
}
