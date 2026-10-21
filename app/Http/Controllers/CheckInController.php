<?php

namespace App\Http\Controllers;

use App\Enums\ScanResult;
use App\Http\Requests\CheckInRequest;
use App\Models\Scan;
use App\Models\Ticket;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function __construct(private CheckInService $checkIn)
    {
    }

    public function create(): View
    {
        $this->authorize('checkIn', Ticket::class);

        return view('pages.check-in.scan', ['title' => 'Check-in']);
    }

    public function store(CheckInRequest $request): RedirectResponse|Response
    {
        $scan = $this->checkIn->register($request->code(), $request->user());

        $scan->load('ticket.event', 'ticket.ticketType', 'manager');

        if ($request->ajax()) {
            return response()->view('pages.check-in._result', ['scan' => $scan]);
        }

        return to_route('check-in.result', $scan);
    }

    public function result(Scan $scan): View
    {
        $this->authorize('checkIn', Ticket::class);

        $scan->load('ticket.event', 'ticket.ticketType', 'manager');

        return view('pages.check-in.result', ['title' => 'Resultado del escaneo', 'scan' => $scan]);
    }

    public function index(Request $request): View
    {
        $this->authorize('checkIn', Ticket::class);

        $scans = Scan::query()
            ->with(['ticket.event', 'manager'])
            ->when(
                $request->filled('result'),
                fn ($query) => $query->where('result', $request->string('result')->toString())
            )
            ->when(
                $request->filled('code'),
                fn ($query) => $query->where('code', 'like', '%'.strtoupper(trim($request->string('code')->toString())).'%')
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totals = Scan::query()
            ->selectRaw('result, count(*) as total')
            ->groupBy('result')
            ->pluck('total', 'result');

        return view('pages.check-in.log', [
            'title' => 'Bitácora de escaneos',
            'scans' => $scans,
            'totals' => $totals,
            'results' => collect(ScanResult::cases())->mapWithKeys(
                fn (ScanResult $result) => [$result->value => $result->describe()]
            ),
        ]);
    }
}
