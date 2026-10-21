<?php

namespace App\Services;

use App\Enums\ScanResult;
use App\Enums\TicketStatus;
use App\Models\Scan;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckInService
{
    public function register(string $code, User $manager): Scan
    {
        $normalized = Str::upper(trim($code));

        if ($normalized === '') {
            return $this->log($normalized, $manager, ScanResult::Invalid, null);
        }

        return DB::transaction(function () use ($normalized, $manager) {
            $ticket = Ticket::query()
                ->where('code', $normalized)
                ->lockForUpdate()
                ->first();

            $result = match (true) {
                $ticket === null => ScanResult::NotFound,
                $ticket->isCancelled() => ScanResult::Cancelled,
                $ticket->wasUsed() || $ticket->status === TicketStatus::Used => ScanResult::AlreadyUsed,
                default => ScanResult::Valid,
            };

            if ($result === ScanResult::Valid) {
                $ticket->update([
                    'status' => TicketStatus::Used,
                    'checked_in_at' => now(),
                ]);
            }

            return $this->log($normalized, $manager, $result, $ticket);
        });
    }

    private function log(string $code, User $manager, ScanResult $result, ?Ticket $ticket): Scan
    {
        return Scan::create([
            'ticket_id' => $ticket?->id,
            'manager_id' => $manager->id,
            'code' => $code,
            'result' => $result,
        ]);
    }
}
