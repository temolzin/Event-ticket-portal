<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boleto {{ $ticket->code }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px;
            background: #f4f4f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
        }
        .ticket {
            max-width: 420px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            overflow: hidden;
        }
        .ticket__head {
            background: #7c3aed;
            color: #ffffff;
            padding: 24px 28px;
        }
        .ticket__label {
            margin: 0 0 6px;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.85;
        }
        .ticket__title {
            margin: 0;
            font-size: 21px;
            font-weight: 700;
        }
        .ticket__body { padding: 24px 28px; }
        .ticket__row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 9px 0;
            border-bottom: 1px solid #f4f4f5;
        }
        .ticket__row:last-child { border-bottom: 0; }
        .ticket__key { font-size: 13px; color: #6b7280; }
        .ticket__value { font-size: 14px; font-weight: 600; text-align: right; }
        .ticket__qr {
            padding: 24px 28px 8px;
            text-align: center;
        }
        .ticket__qr img,
        .ticket__qr svg {
            width: 220px;
            height: 220px;
        }
        .ticket__code {
            padding: 8px 28px 28px;
            text-align: center;
        }
        .ticket__code p { margin: 0; }
        .ticket__code-label { font-size: 11px; color: #9ca3af; margin-bottom: 4px !important; }
        .ticket__code-value {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: #374151;
        }
        .ticket__foot {
            padding: 16px 28px 22px;
            border-top: 1px solid #e4e4e7;
            text-align: center;
        }
        .ticket__foot p { margin: 0; font-size: 12px; color: #9ca3af; }
        .no-print {
            max-width: 420px;
            margin: 0 auto 20px;
            text-align: center;
        }
        .no-print button {
            background: #7c3aed;
            color: #ffffff;
            border: 0;
            border-radius: 10px;
            padding: 11px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        @media print {
            body { padding: 0; background: #ffffff; }
            .no-print { display: none; }
            .ticket { border: 1px solid #d4d4d8; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()">Imprimir boleto</button>
    </div>

    <div class="ticket">
        <div class="ticket__head">
            <p class="ticket__label">Boleto de entrada</p>
            <h1 class="ticket__title">{{ $ticket->event->name }}</h1>
        </div>

        <div class="ticket__body">
            <div class="ticket__row">
                <span class="ticket__key">Titular</span>
                <span class="ticket__value">{{ $ticket->buyer_name }}</span>
            </div>
            <div class="ticket__row">
                <span class="ticket__key">Fecha</span>
                <span class="ticket__value">{{ $ticket->event->starts_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="ticket__row">
                <span class="ticket__key">Lugar</span>
                <span class="ticket__value">{{ $ticket->event->venue }}</span>
            </div>
            <div class="ticket__row">
                <span class="ticket__key">Tipo de boleto</span>
                <span class="ticket__value">{{ $ticket->ticketType->name }}</span>
            </div>
            <div class="ticket__row">
                <span class="ticket__key">Precio</span>
                <span class="ticket__value">${{ number_format((float) $ticket->price, 2) }}</span>
            </div>
        </div>

        <div class="ticket__qr">
            {!! $ticket->qrCode() !!}
        </div>

        <div class="ticket__code">
            <p class="ticket__code-label">Código del boleto</p>
            <p class="ticket__code-value">{{ $ticket->code }}</p>
        </div>

        <div class="ticket__foot">
            <p>Presenta este boleto en la entrada. No es transferible.</p>
        </div>
    </div>
</body>
</html>
