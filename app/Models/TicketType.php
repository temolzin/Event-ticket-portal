<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'key',
        'name',
        'seats_per_ticket',
        'early_price',
        'regular_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'early_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'seats_per_ticket' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function hasSales(): bool
    {
        return $this->tickets()->exists();
    }

    public function priceForPhase(bool $earlyPhase): float
    {
        $early = $earlyPhase && $this->early_price !== null;

        return (float) ($early ? $this->early_price : $this->regular_price);
    }

    public function canBeDeleted(): bool
    {
        return ! $this->hasSales();
    }
}
