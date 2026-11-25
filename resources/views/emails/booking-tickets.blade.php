<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#f4f0e8;color:#221c14;font-family:Arial,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f0e8">
<tr><td align="center" style="padding:24px 12px">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;text-align:left">
        <tr><td style="padding:24px;background:#201b15;color:#f8d790">
            <p style="margin:0;font-size:14px;letter-spacing:2px">YALLA TEER</p>
            <h1 style="margin:8px 0;font-size:28px;color:#fff">{{ __('Your e-tickets') }}</h1>
            <p style="margin:0">{{ __('Booking reference') }} {{ $booking->code }}</p>
        </td></tr>
        @foreach($booking->passengers as $passenger)
            @continue($passenger->tickets->isEmpty())
            <tr><td style="padding-top:18px">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fffdf7;border:1px solid #dbc99f">
                    <tr><td style="padding:18px;background:#f8d790">
                        <p style="margin:0;font-size:12px">{{ __('PASSENGER') }}</p>
                        <h2 style="margin:4px 0 0;font-size:21px">{{ $passenger->first_name }} {{ $passenger->last_name }}</h2>
                    </td></tr>
                    @foreach($passenger->tickets->sortBy(fn ($ticket) => $ticket->bookingFlight->flight->departure_at) as $ticket)
                        @php($leg = $ticket->bookingFlight)
                        @php($flight = $leg->flight)
                        <tr><td style="padding:18px;border-bottom:1px solid #eadfc6">
                            <p style="margin:0 0 6px">{{ __(ucfirst($leg->direction)) }} · {{ $flight->flight_number }}</p>
                            <p style="margin:0 0 8px;font-size:22px"><strong>{{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}</strong></p>
                            <p style="margin:0 0 6px">{{ __('Departure') }} {{ $flight->departure_at->format('d M Y, H:i T') }}</p>
                            <p style="margin:0 0 6px">{{ __('Cabin') }} {{ __(ucfirst($leg->flightClass->class)) }} · Seat {{ $ticket->seat?->seat_number ?? 'Unassigned' }}</p>
                            <p style="margin:0">{{ __('Ticket number') }} <strong>{{ $ticket->ticket_number }}</strong></p>
                            <p style="margin:14px 0 0">
                                <a href="{{ route('my.ticket', $ticket->ticket_number) }}" style="display:inline-block;padding:10px 14px;border-radius:6px;background:#201b15;color:#f8d790;text-decoration:none;font-weight:bold">{{ __('View ticket') }}</a>
                            </p>
                        </td></tr>
                    @endforeach
                </table>
            </td></tr>
        @endforeach
    </table>
</td></tr>
</table>
</body>
</html>
