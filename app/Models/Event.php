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
        return $this->activeTickets()->count();
    }

    public function countAvailableSeats(): int
    {
        return max($this->capacity - $this->countSoldSeats(), 0);
    }

    public function hasAvailableSeats(): bool
    {
        return $this->countAvailableSeats() > 0;
    }
}
