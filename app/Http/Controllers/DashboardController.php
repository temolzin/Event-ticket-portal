<?php

namespace App\Http\Controllers;

use App\Enums\ScanResult;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Scan;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function showDashboard(Request $request): View
    {
        $viewer = $request->user();

        return view('pages.dashboard.index', [
            'title' => __('Dashboard'),
            'viewer' => $viewer,
            'events' => $this->buildEventCards($viewer),
            'activity' => $this->getRecentActivity($viewer),
            'primaryAction' => $this->buildPrimaryAction($viewer->role),
            'activityTitle' => $viewer->isSeller()
                ? __('Recent ticket sales')
                : __('Recent check-in activity'),
            'isSeller' => $viewer->isSeller(),
        ]);
    }

    private function getActiveEventQuery(): Builder
    {
        return Event::query()
            ->where('is_active', true)
            ->where('starts_at', '>=', now()->startOfDay()->subDay());
    }

    private function getActiveTicketsQuery(): Builder
    {
        return Ticket::query()
            ->active()
            ->whereHas('event', fn (Builder $eventQuery) => $eventQuery
                ->where('is_active', true)
                ->where('starts_at', '>=', now()->startOfDay()->subDay()));
    }

    private function getDailyEventScanCounts(User $viewer): Collection
    {
        return DB::table('scans')
            ->join('tickets', 'tickets.id', '=', 'scans.ticket_id')
            ->join('events', 'events.id', '=', 'tickets.event_id')
            ->select('tickets.event_id', 'scans.result')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('scans.created_at', [today()->startOfDay(), today()->endOfDay()])
            ->where('events.is_active', true)
            ->where('events.starts_at', '>=', now()->startOfDay()->subDay())
            ->when(
                $viewer->isManager(),
                fn (QueryBuilder $scanQuery) => $scanQuery
                    ->where('scans.manager_id', $viewer->id)
            )
            ->groupBy('tickets.event_id', 'scans.result')
            ->get()
            ->groupBy('event_id')
            ->map(fn (Collection $eventScans) => [
                'allowed' => (int) $eventScans
                    ->firstWhere('result', ScanResult::Valid->value)?->total,
                'denied' => (int) $eventScans
                    ->reject(fn (object $scan) => $scan->result === ScanResult::Valid->value)
                    ->sum('total'),
            ]);
    }

    private function buildEventCards(User $viewer): Collection
    {
        $dailyEventScanCounts = $this->getDailyEventScanCounts($viewer);

        $eventCards = $this->getActiveEventQuery()
            ->withCount([
                'tickets as sold_tickets_count' => fn (Builder $ticketQuery) => $ticketQuery->active(),
                'tickets as checked_in_tickets_count' => fn (Builder $ticketQuery) => $ticketQuery
                    ->active()
                    ->whereNotNull('checked_in_at'),
                'tickets as viewer_sold_tickets_count' => fn (Builder $ticketQuery) => $ticketQuery
                    ->active()
                    ->where('seller_id', $viewer->id),
                'tickets as viewer_sent_tickets_count' => fn (Builder $ticketQuery) => $ticketQuery
                    ->active()
                    ->where('seller_id', $viewer->id)
                    ->whereNotNull('sent_at'),
            ])
            ->orderBy('starts_at')
            ->limit(6)
            ->get();

        return $eventCards->map(function (Event $event) use ($dailyEventScanCounts, $viewer) {
            $todayScans = $dailyEventScanCounts->get($event->id, [
                'allowed' => 0,
                'denied' => 0,
            ]);

            return [
                'event' => $event,
                'sold' => $event->sold_tickets_count,
                'checkedIn' => $event->checked_in_tickets_count,
                'salesPercentage' => min(
                    (int) round($event->sold_tickets_count / max($event->capacity, 1) * 100),
                    100
                ),
                'metrics' => $this->buildEventMetrics($viewer, $event, $todayScans),
                'action' => $this->buildEventAction($viewer->role, $event),
            ];
        });
    }

    private function buildEventMetrics(User $viewer, Event $event, array $todayScans): array
    {
        $commonMetrics = [
            [
                'key' => 'checked-in',
                'label' => __('Tickets checked in'),
                'value' => $event->checked_in_tickets_count,
                'tone' => 'success',
            ],
            [
                'key' => 'seats-available',
                'label' => __('Seats available'),
                'value' => max($event->capacity - $event->sold_tickets_count, 0),
                'tone' => 'brand',
            ],
        ];

        return match ($viewer->role) {
            UserRole::Admin, UserRole::Manager => array_merge($commonMetrics, [
                [
                    'key' => 'allowed-today',
                    'label' => __('Allowed today'),
                    'value' => $todayScans['allowed'],
                    'tone' => 'success',
                ],
                [
                    'key' => 'denied-today',
                    'label' => __('Denied today'),
                    'value' => $todayScans['denied'],
                    'tone' => 'error',
                ],
            ]),
            UserRole::Seller => array_merge($commonMetrics, [
                [
                    'key' => 'your-sales',
                    'label' => __('Your tickets sold'),
                    'value' => $event->viewer_sold_tickets_count,
                    'tone' => 'blue',
                ],
                [
                    'key' => 'tickets-sent',
                    'label' => __('Tickets sent'),
                    'value' => $event->viewer_sent_tickets_count,
                    'tone' => 'warning',
                ],
            ]),
        };
    }

    private function buildEventAction(UserRole $role, Event $event): array
    {
        return match ($role) {
            UserRole::Admin => [
                'label' => __('Manage event'),
                'url' => route('events.show', $event),
            ],
            UserRole::Seller => [
                'label' => __('Register a sale'),
                'url' => route('tickets.create'),
            ],
            UserRole::Manager => [
                'label' => __('Open check-in'),
                'url' => route('check-in.scan'),
            ],
        };
    }

    private function buildPrimaryAction(UserRole $role): array
    {
        return match ($role) {
            UserRole::Admin => [
                'label' => __('Create event'),
                'url' => route('events.create'),
            ],
            UserRole::Seller => [
                'label' => __('Register a sale'),
                'url' => route('tickets.create'),
            ],
            UserRole::Manager => [
                'label' => __('Open check-in'),
                'url' => route('check-in.scan'),
            ],
        };
    }

    private function getRecentActivity(User $viewer): Collection
    {
        return match ($viewer->role) {
            UserRole::Seller => $this->getRecentSales($viewer),
            default => $this->getRecentScans($viewer),
        };
    }

    private function getRecentSales(User $seller): Collection
    {
        return $this->getActiveTicketsQuery()
            ->where('seller_id', $seller->id)
            ->with('event')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Ticket $ticket) => [
                'event' => $ticket->event->name,
                'detail' => __('Ticket sale'),
                'reference' => $ticket->code,
                'status' => __($ticket->status->describe()),
                'tone' => match ($ticket->status) {
                    TicketStatus::Used => 'success',
                    TicketStatus::Sent => 'blue',
                    default => 'warning',
                },
                'actor' => $ticket->buyer_name,
                'occurredAt' => $ticket->created_at,
                'url' => route('tickets.show', $ticket),
            ]);
    }

    private function getRecentScans(User $viewer): Collection
    {
        return Scan::query()
            ->with(['ticket.event', 'manager'])
            ->when(
                $viewer->isManager(),
                fn (Builder $scanQuery) => $scanQuery->where('manager_id', $viewer->id)
            )
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Scan $scan) => [
                'event' => $scan->ticket?->event?->name ?? __('Unknown event'),
                'detail' => $scan->code,
                'reference' => $scan->ticket?->buyer_name ?? __('Ticket not found'),
                'status' => __($scan->result->describe()),
                'tone' => $scan->result->isAllowed() ? 'success' : 'error',
                'actor' => $scan->manager?->name ?? __('Unknown manager'),
                'occurredAt' => $scan->created_at,
                'url' => route('check-in.log'),
            ]);
    }
}
