<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu boleto</title>
</head>
<body style="margin:0;padding:24px;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background-color:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e4e4e7;">
        <tr>
            <td style="padding:28px 32px 12px 32px;">
                <p style="margin:0 0 4px 0;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;color:#7c3aed;font-weight:600;">
                    Boleto digital
                </p>
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#111827;">
                    {{ $ticket->event->name }}
                </h1>
            </td>
        </tr>

        <tr>
            <td style="padding:16px 32px 0 32px;">
                <p style="margin:0 0 20px 0;font-size:15px;line-height:1.6;color:#4b5563;">
                    Hola {{ $ticket->buyer_name }}, preséntalo en la entrada junto con tu identificación.
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:0 32px 8px 32px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafafa;border:1px solid #e4e4e7;border-radius:10px;">
                    <tr>
                        <td style="padding:18px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#6b7280;">Fecha</td>
                                    <td align="right" style="padding:4px 0;font-size:14px;font-weight:600;color:#111827;">
                                        {{ $ticket->event->starts_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#6b7280;">Lugar</td>
                                    <td align="right" style="padding:4px 0;font-size:14px;font-weight:600;color:#111827;">
                                        {{ $ticket->event->venue }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#6b7280;">Boleto</td>
                                    <td align="right" style="padding:4px 0;font-size:14px;font-weight:600;color:#111827;">
                                        {{ $ticket->ticketType->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#6b7280;">Precio</td>
                                    <td align="right" style="padding:4px 0;font-size:14px;font-weight:600;color:#111827;">
                                        ${{ number_format((float) $ticket->price, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding:20px 32px 8px 32px;">
                <div style="display:inline-block;padding:16px;border:1px solid #e4e4e7;border-radius:12px;background-color:#ffffff;">
                    <img
                        src="cid:{{ \App\Mail\TicketMail::QR_CID }}"
                        alt="Código QR del boleto"
                        width="220"
                        height="220"
                        style="display:block;width:220px;height:220px;border:0;"
                    >
                </div>
                <p style="margin:12px 0 0 0;font-size:13px;color:#6b7280;">
                    Presenta este código en la entrada
                </p>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding:12px 32px 24px 32px;">
                <p style="margin:0 0 6px 0;font-size:12px;color:#9ca3af;">Código del boleto</p>
                <p style="margin:0;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:15px;font-weight:600;letter-spacing:0.04em;color:#374151;">
                    {{ $ticket->code }}
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:18px 32px 28px 32px;border-top:1px solid #e4e4e7;">
                <p style="margin:0;font-size:12px;line-height:1.6;color:#9ca3af;">
                    Si necesitas cambiar o cancelar este boleto, comunícate con el equipo que lo registró.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
