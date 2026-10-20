<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'seller_id',
        'buyer_name',
        'buyer_email',
        'buyer_photo_path',
        'payment_receipt_path',
        'code',
        'status',
        'sent_at',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'sent_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }
}
