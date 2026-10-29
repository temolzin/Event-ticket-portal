<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'venue',
        'starts_at',
        'capacity',
        'early_quantity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function activeTicketTypes(): HasMany
    {
        return $this->ticketTypes()->where('is_active', true);
    }

    public function activeTickets(): HasMany
    {
        return $this->tickets()->whereNot('status', TicketStatus::Cancelled);
    }

    public function cancelledTickets(): HasMany
    {
        return $this->tickets()->where('status', TicketStatus::Cancelled);
    }

    public function countSoldSeats(): int
    {
        return (int) $this->activeTickets()
            ->join('ticket_types', 'tickets.ticket_type_id', '=', 'ticket_types.id')
            ->sum('ticket_types.seats_per_ticket');
    }

    public function countSoldTickets(): int
    {
        return $this->activeTickets()->count();
    }

    public function countAvailableSeats(): int
    {
        return max($this->capacity - $this->countSoldSeats(), 0);
    }

    public function hasAvailableSeats(int $seatsRequired = 1): bool
    {
        return $this->countAvailableSeats() >= $seatsRequired;
    }

    public function isInEarlyPhase(): bool
    {
        return $this->countSoldTickets() < $this->early_quantity;
    }

    public function hasEnded(): bool
    {
        return $this->starts_at->copy()->addDays(2)->startOfDay()->isPast();
    }

    public function canBeDeleted(): bool
    {
        return $this->hasEnded() && ! $this->activeTickets()->exists();
    }

    public function deleteBlockReason(): ?string
    {
        if (! $this->hasEnded()) {
            return 'No se puede eliminar el evento: aún no ha finalizado (se desbloquea un día después del evento).';
        }

        if ($this->activeTickets()->exists()) {
            return 'No se puede eliminar el evento: tiene boletos vendidos.';
        }

        return null;
    }
}
