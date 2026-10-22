<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Badges for :event', ['event' => $event->name]) }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
            color: #1f2937;
            font-family: Arial, Helvetica, sans-serif;
        }
        .print-controls {
            margin: 0 auto 20px;
            text-align: center;
        }
        .print-controls button {
            border: 0;
            border-radius: 8px;
            background: #465fff;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            padding: 11px 20px;
        }
        .badge {
            display: flex;
            flex-direction: column;
            width: 54mm;
            height: 86mm;
            margin: 0 auto 8mm;
            overflow: hidden;
            padding: 4mm;
            border: 1px solid #9ca3af;
            border-radius: 3mm;
            background: #fff;
            text-align: center;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .badge__event {
            border-bottom: 1px solid #d1d5db;
            font-size: 10pt;
            font-weight: 700;
            padding: 2mm 1mm;
        }
        .badge__main {
            align-items: center;
            display: flex;
            flex: 1;
            flex-direction: column;
            justify-content: center;
            gap: 3mm;
        }
        .badge__photo,
        .badge__photo-placeholder {
            align-items: center;
            border: 1px solid #d1d5db;
            display: flex;
            flex: 0 0 27mm;
            height: 27mm;
            justify-content: center;
            object-fit: cover;
            width: 27mm;
        }
        .badge__photo-placeholder {
            color: #6b7280;
            font-size: 7pt;
        }
        .badge__name {
            display: block;
            font-size: 12pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }
        .badge__type,
        .badge__event-info {
            font-size: 8pt;
            margin: 2mm 0 0;
        }
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            body { padding: 0; background: #fff; }
            .print-controls { display: none; }
            .badge { border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button type="button" onclick="window.print()">{{ __('Print badges') }}</button>
    </div>

    @foreach($tickets as $ticket)
        <article class="badge">
            <header class="badge__event">{{ $event->name }}</header>
            <div class="badge__main">
                @if($ticket->buyer_photo_path)
                    <img
                        src="{{ route('tickets.photo', $ticket) }}"
                        alt="{{ __('Photo of :name', ['name' => $ticket->buyer_name]) }}"
                        class="badge__photo"
                    >
                @else
                    <div class="badge__photo-placeholder">{{ __('No photo') }}</div>
                @endif
                <div>
                    <strong class="badge__name">{{ $ticket->buyer_name }}</strong>
                    <p class="badge__type">{{ $ticket->ticketType->name }}</p>
                    <p class="badge__event-info">{{ $event->starts_at->translatedFormat('d M Y, H:i') }}</p>
                    <p class="badge__event-info">{{ $event->venue }}</p>
                </div>
            </div>
        </article>
    @endforeach
</body>
</html>
