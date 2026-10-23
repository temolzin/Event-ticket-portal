<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Helpers\QrCodeHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'ticket_type_id',
        'seller_id',
        'buyer_name',
        'buyer_email',
        'buyer_photo_path',
        'payment_receipt_path',
        'code',
        'status',
        'price',
        'sent_at',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'price' => 'decimal:2',
            'sent_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNot('status', TicketStatus::Cancelled);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', TicketStatus::Cancelled);
    }

    public function scopeSoldBy(Builder $query, int $sellerId): Builder
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeMatchingCancelledClient(Builder $query, string $name, string $email): Builder
    {
        $name = mb_strtolower(trim($name));
        $email = mb_strtolower(trim($email));

        return $query->where(function (Builder $matches) use ($name, $email) {
            if ($name === '' && $email === '') {
                $matches->whereRaw('1 = 0');

                return;
            }

            if ($name !== '') {
                $matches->whereRaw('LOWER(TRIM(buyer_name)) = ?', [$name]);
            }

            if ($email !== '') {
                $method = $name === '' ? 'whereRaw' : 'orWhereRaw';
                $matches->{$method}('LOWER(TRIM(buyer_email)) = ?', [$email]);
            }
        });
    }

    public function isCancelled(): bool
    {
        return $this->status === TicketStatus::Cancelled;
    }

    public function wasUsed(): bool
    {
        return $this->checked_in_at !== null;
    }

    public static function generateCode(): string
    {
        return (string) Str::ulid();
    }

    public function storeBuyerPhoto(UploadedFile $photo): void
    {
        $this->buyer_photo_path = $photo->store('buyer-photos', 'local');
    }

    public function storePaymentReceipt(UploadedFile $receipt): void
    {
        $this->payment_receipt_path = $receipt->store('payment-receipts', 'local');
    }

    public function buyerPhotoPath(): ?string
    {
        return $this->buyer_photo_path
            ? Storage::disk('local')->path($this->buyer_photo_path)
            : null;
    }

    public function paymentReceiptPath(): ?string
    {
        return $this->payment_receipt_path
            ? Storage::disk('local')->path($this->payment_receipt_path)
            : null;
    }

    public function qrCode(): string
    {
        return QrCodeHelper::svg($this->code);
    }

    public function qrPng(): string
    {
        return QrCodeHelper::png($this->code);
    }

    public function encryptedDownloadKey(): string
    {
        return rtrim(strtr(Crypt::encryptString((string) $this->getKey()), '+/', '-_'), '=');
    }

    public function wasSent(): bool
    {
        return $this->sent_at !== null;
    }
}
