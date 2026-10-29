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
            max-width: 900px;
            text-align: center;
        }
        .print-controls p {
            color: #4b5563;
            font-size: 13px;
            margin: 10px 0 0;
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
        .badge-pair {
            display: grid;
            grid-template-columns: repeat(2, 54mm);
            gap: 4mm;
            justify-content: center;
            margin: 0 auto 8mm;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .badge {
            display: flex;
            flex-direction: column;
            width: 54mm;
            height: 86mm;
            overflow: hidden;
            padding: 4mm;
            border: 1px solid #087e8b;
            border-radius: 4mm;
            background: #f3efe6;
            color: #102a32;
            break-inside: avoid;
            page-break-inside: avoid;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
            isolation: isolate;
            position: relative;
        }
        .badge::before {
            position: absolute;
            inset: 0;
            background: #087e8b;
            clip-path: polygon(
                0 0, 11% 0, 11% 18%, 7% 38%, 10% 57%, 12% 76%,
                10% 100%, 0 100%, 0 72%, 4% 55%, 0 37%
            );
            content: "";
            pointer-events: none;
            z-index: 0;
        }
        .badge::after {
            position: absolute;
            inset: 0;
            background: #d98752;
            clip-path: polygon(
                7% 0, 10% 0, 10% 20%, 6% 39%, 9% 58%, 11% 76%,
                9% 100%, 7% 100%, 8% 77%, 5% 58%, 2% 39%, 7% 20%
            );
            content: "";
            pointer-events: none;
            z-index: 0;
        }
        .badge--back::before,
        .badge--back::after {
            transform: scaleX(-1);
        }
        .badge > * {
            position: relative;
            z-index: 1;
        }
        .badge__event {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 15mm;
            border-bottom: 0.6mm solid #d98752;
            padding: 2mm 1mm 3mm;
            text-align: center;
        }
        .badge__event-name {
            color: #102a32;
            font-size: 10pt;
            font-weight: 700;
            line-height: 1.15;
            white-space: nowrap;
        }
        .badge__footer {
            align-items: center;
            border-top: 0.6mm solid #d98752;
            display: flex;
            justify-content: center;
            margin-top: auto;
            min-height: 9mm;
            padding: 0 1mm;
            z-index: 2;
        }
        .badge__logo {
            display: block;
            width: auto !important;
            height: auto !important;
            max-width: 28mm !important;
            max-height: 8mm !important;
            object-fit: contain;
        }
        .badge__main {
            align-items: center;
            display: flex;
            flex: 1;
            flex-direction: column;
            justify-content: center;
            gap: 2.5mm;
            min-height: 0;
            padding-top: 2mm;
        }
        .badge__photo {
            border: 1.2mm solid #ffffff;
            border-radius: 2mm;
            box-shadow: 0 0 0 0.8mm #d98752, 0 1mm 4mm rgba(16, 42, 50, 0.2);
            flex: 0 0 27mm;
            height: 27mm;
            object-fit: cover;
            width: 27mm;
        }
        .badge__photo-placeholder {
            align-items: center;
            background: rgba(8, 126, 139, 0.1);
            border: 0.8mm solid #d98752;
            border-radius: 2mm;
            color: #102a32;
            display: flex;
            flex: 0 0 27mm;
            font-size: 7pt;
            height: 27mm;
            justify-content: center;
            text-align: center;
            width: 27mm;
        }
        .badge__details {
            min-width: 0;
            text-align: center;
            width: 100%;
        }
        .badge__name {
            display: block;
            font-size: 12pt;
            font-weight: 700;
            line-height: 1.12;
            overflow-wrap: anywhere;
        }
        .badge__type {
            color: #087e8b;
            font-size: 8pt;
            margin-top: 2mm;
        }
        .badge__event-info {
            color: #102a32;
            font-size: 7pt;
            margin-top: 0.8mm;
        }
        .badge--back {
            align-items: center;
            justify-content: center;
            padding: 5mm;
            text-align: center;
        }
        .badge__back-content {
            align-items: center;
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 3mm;
            justify-content: center;
            width: 100%;
        }
        .badge__back-mark {
            border: 0.4mm solid #087e8b;
            border-radius: 50%;
            height: 16mm;
            margin-bottom: 1mm;
            position: relative;
            width: 16mm;
        }
        .badge__back-mark::before {
            border: 0.3mm dashed #d98752;
            border-radius: 50%;
            content: "";
            inset: 2mm;
            position: absolute;
        }
        .badge__back-mark::after {
            background: #087e8b;
            border-radius: 50%;
            box-shadow: -9mm 7mm 0 #d98752, 5mm 9mm 0 #102a32;
            content: "";
            height: 1.5mm;
            position: absolute;
            right: 1.5mm;
            top: 2mm;
            width: 1.5mm;
        }
        .badge__back-tagline {
            color: #087e8b;
            font-size: 9pt;
            font-weight: 700;
            line-height: 1.25;
            margin: 0;
        }
        .badge__back-label {
            color: #d98752;
            font-size: 7pt;
            letter-spacing: 0.8px;
            margin: 1mm 0 0;
            text-transform: uppercase;
        }
        .badge__back-event {
            color: #102a32;
            font-size: 12pt;
            font-weight: 700;
            line-height: 1.15;
            margin: 1mm 0 0;
            overflow-wrap: anywhere;
        }
        .badge__back-info {
            color: #102a32;
            font-size: 8pt;
            line-height: 1.3;
            margin: 0;
            overflow-wrap: anywhere;
        }
        .badge__back-link {
            border-top: 0.6mm solid #d98752;
            color: #087e8b;
            font-size: 7pt;
            margin-top: auto;
            padding: 2mm 1mm;
            position: relative;
            text-align: center;
            text-decoration: none;
            width: 100%;
            z-index: 2;
        }
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            body { padding: 0; background: #fff; }
            .print-controls { display: none; }
            .badge-pair { margin-bottom: 4mm; }
            .badge { border-radius: 2mm; }
        }
        @media screen and (max-width: 640px) {
            body { padding: 12px; }
            .badge-pair {
                grid-template-columns: 54mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button type="button" onclick="window.print()">{{ __('Print badges') }}</button>
        <p>{{ __('The front and back of each badge print together on the same sheet.') }}</p>
    </div>

    @foreach($tickets as $ticket)
        <main class="badge-pair">
            <article class="badge badge--front">
                <header class="badge__event">
                    <span class="badge__event-name">{{ $event->name }}</span>
                </header>
                <div class="badge__main">
                    @if($ticket->buyer_photo_path)
                        <img
                            src="{{ route('tickets.photo', $ticket->encryptedDownloadKey()) }}"
                            alt="{{ __('Photo of :name', ['name' => $ticket->buyer_name]) }}"
                            class="badge__photo"
                        >
                    @else
                        <div class="badge__photo-placeholder">{{ __('No photo') }}</div>
                    @endif
                    <div class="badge__details">
                        <strong class="badge__name">{{ $ticket->buyer_name }}</strong>
                        <p class="badge__type">{{ $ticket->ticketType->name }}</p>
                        <p class="badge__event-info">{{ $event->starts_at->translatedFormat('d M Y, H:i') }}</p>
                        <p class="badge__event-info">{{ $event->venue }}</p>
                    </div>
                </div>
                <footer class="badge__footer">
                    <img
                        src="{{ asset('images/badges/company-logo.png') }}"
                        alt="{{ __('Company logo') }}"
                        class="badge__logo"
                    >
                </footer>
            </article>
            <article class="badge badge--back badge-pair__back">
                <div class="badge__back-content">
                    <div class="badge__back-mark" aria-hidden="true"></div>
                    <p class="badge__back-tagline">{{ __('Connecting ideas, creating the future.') }}</p>
                    <div>
                        <p class="badge__back-label">{{ __('I was part of') }}</p>
                        <p class="badge__back-event">{{ $event->name }}</p>
                    </div>
                </div>
                <a class="badge__back-link" href="https://rootheim.com/" title="{{ __('Root Heim Company') }}">
                    {{ __('Root Heim Company') }}<br>
                    rootheim.com
                </a>
            </article>
        </main>
    @endforeach
</body>
</html>
