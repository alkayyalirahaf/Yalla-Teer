<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <script src="{{ asset('assets/theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/theme-switch.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('assets/assistant-shortcut.css') }}">
    <title>{{ __('Ticket') }} {{ $ticket->ticket_number }} · Yalla Teer</title>
    <style id="ticket-styles">
        * { box-sizing: border-box; }
        body { margin: 0; padding: clamp(16px, 5vw, 56px); background: #17130e; color: #292017; font: 16px system-ui, sans-serif; }
        main { max-width: 940px; margin: auto; }
        nav { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        nav a { margin-inline-end: auto; }
        nav a { color: #f5d48d; }
        .ticket-action { display: grid; place-items: center; width: 44px; height: 44px; padding: 0; border: 0; background: transparent; color: #f5d48d; cursor: pointer; }
        .ticket-action:hover { color: #fffaf0; }
        .ticket-action:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; border-radius: 6px; }
        button:disabled { opacity: .65; cursor: wait; }
        .download-error { color: #f5d48d; margin-bottom: 16px; }
        article { display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 1fr); overflow: hidden; border: 1px solid #bfa06a; border-radius: 16px; background: #fffaf0; }
        header { min-height: 88px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px 16px; padding: 16px 24px; background: #211911; color: #f5d48d; border-bottom: 3px solid #bfa06a; }
        section { padding: 24px; }
        h1, h2 { margin: 0; }
        h1 { font-size: 1.35rem; }
        h2 { font-size: 1.2rem; overflow-wrap: anywhere; }
        p { margin: 6px 0; }
        .route { font-size: clamp(1.65rem, 5vw, 2.6rem); font-weight: 800; }
        .label { color: #67543c; font-size: .75rem; text-transform: uppercase; letter-spacing: .09em; }
        .cabin { font-size: .8rem; text-transform: uppercase; letter-spacing: .1em; }
        .stub { min-width: 0; border-inline-start: 2px dashed #bfa06a; background: #f5ecdb; }
        .stub header { font-size: .85rem; font-weight: 800; letter-spacing: .07em; }
        .stub dl { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ticket-id { margin-top: 20px; padding-top: 12px; border-top: 1px solid #dbc9a7; overflow-wrap: anywhere; }
        dl { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 155px), 1fr)); gap: 16px; margin-bottom: 0; }
        dt { font-size: .85rem; }
        dd { margin: 3px 0 0; font-weight: 700; overflow-wrap: anywhere; }
        @media screen and (max-width: 640px) {
            body { padding: 12px; }
            nav { flex-wrap: wrap; }
            article { grid-template-columns: 1fr; }
            .stub { border-inline-start: 0; border-block-start: 2px dashed #bfa06a; }
            header { min-height: auto; }
        }
        @media print {
            body { background: white; padding: 0; }
            nav, .download-error { display: none; }
            article { break-inside: avoid; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
    <style media="screen">
        body { background: var(--ui-bg); color: var(--ui-text); }
        article { background: var(--ui-surface); border-color: var(--ui-border); border-radius: 8px; }
        header { background: var(--ui-secondary); color: white; border-color: var(--ui-brand); }
        .stub { background: var(--ui-raised); border-color: var(--ui-border); }
        nav a,.ticket-action,.ticket-action:hover { color: var(--ui-link); }
        .download-error { color: var(--ui-danger); }
        .label { color: var(--ui-muted); }
    </style>
</head>
<body>
    @php($leg = $ticket->bookingFlight)
    @php($flight = $leg->flight)
    <main>
        <nav aria-label="{{ __('Ticket actions') }}">
            <x-theme-switch />
            <a href="{{ route('my.booking', $leg->booking->code) }}">{{ __('Back to booking') }}</a>
            <button class="ticket-action" type="button" aria-label="{{ __('Print') }}" title="{{ __('Print') }}" onclick="window.print()">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 9V3h12v6M6 17H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M6 14h12v7H6zM6 12h1"/>
                </svg>
            </button>
            <button id="download-ticket" class="ticket-action" type="button" aria-label="{{ __('Download PDF') }}" title="{{ __('Download PDF') }}">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3v12m-5-5 5 5 5-5M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>
                </svg>
            </button>
        </nav>
        <p id="download-error" class="download-error" role="alert" hidden>{{ __('Could not download the PDF. Please try again.') }}</p>
        <article id="ticket" data-number="{{ $ticket->ticket_number }}">
            <div>
            <header>
                <div><p class="cabin">{{ __('Yalla Teer') }} <span aria-hidden="true">✈</span></p><h1>{{ __('Electronic ticket') }}</h1></div>
                <strong class="cabin">{{ $leg->flightClass->class }}</strong>
            </header>
            <section>
                <p class="label">{{ __('Passenger') }}</p>
                <h2>{{ $ticket->passenger->first_name }} {{ $ticket->passenger->last_name }}</h2>
                <p class="route">{{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}</p>
                <dl>
                    <div><dt>{{ __('Booking reference') }}</dt><dd>{{ $leg->booking->code }}</dd></div>
                    <div><dt>{{ __('Flight') }}</dt><dd>{{ $flight->flight_number }} · {{ $flight->airline->name }}</dd></div>
                    <div><dt>{{ __('Departure') }}</dt><dd>{{ $flight->departure_at->format('d M Y, H:i T') }}</dd></div>
                    <div><dt>{{ __('Cabin') }}</dt><dd>{{ __(ucfirst($leg->flightClass->class)) }}</dd></div>
                    <div><dt>{{ __('Seat') }}</dt><dd>{{ $ticket->seat?->seat_number ?? 'Unassigned' }}</dd></div>
                </dl>
                <p class="ticket-id">{{ __('Ticket number') }} <strong>{{ $ticket->ticket_number }}</strong></p>
            </section>
            </div>
            <aside class="stub" aria-label="{{ __('Ticket stub') }}">
                <header>YALLA TEER <span aria-hidden="true">✈</span></header>
                <section>
                    <p class="label">{{ __('Passenger') }}</p>
                    <h2>{{ $ticket->passenger->first_name }} {{ $ticket->passenger->last_name }}</h2>
                    <p>{{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}</p>
                    <dl>
                        <div><dt>{{ __('Flight') }}</dt><dd>{{ $flight->flight_number }}</dd></div>
                        <div><dt>{{ __('Seat') }}</dt><dd>{{ $ticket->seat?->seat_number ?? 'Unassigned' }}</dd></div>
                        <div><dt>{{ __('Date') }}</dt><dd>{{ $flight->departure_at->format('d M') }}</dd></div>
                        <div><dt>{{ __('Time') }}</dt><dd>{{ $flight->departure_at->format('H:i') }}</dd></div>
                    </dl>
                    <p class="ticket-id">{{ $ticket->ticket_number }}</p>
                </section>
            </aside>
        </article>
    </main>
    @unless(request()->is('admin', 'admin/*', 'airline', 'airline/*'))
        <x-assistant-shortcut />
    @endunless
    <script>
        function ticketPdf(canvas) {
            const jpeg = Uint8Array.from(atob(canvas.toDataURL('image/jpeg', .95).split(',')[1]), byte => byte.charCodeAt(0));
            const width = Math.min(720, canvas.width / 2);
            const height = Math.round(width * canvas.height / canvas.width);
            const commands = `q ${width} 0 0 ${height} 18 18 cm /Ticket Do Q`;
            const objects = [
                ['<< /Type /Catalog /Pages 2 0 R >>'],
                ['<< /Type /Pages /Kids [3 0 R] /Count 1 >>'],
                [`<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${width + 36} ${height + 36}] /Resources << /XObject << /Ticket 4 0 R >> >> /Contents 5 0 R >>`],
                [`<< /Type /XObject /Subtype /Image /Width ${canvas.width} /Height ${canvas.height} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${jpeg.length} >>\nstream\n`, jpeg, '\nendstream'],
                [`<< /Length ${commands.length} >>\nstream\n${commands}\nendstream`],
            ];
            const parts = ['%PDF-1.4\n'];
            const offsets = [];
            let size = parts[0].length;
            for (const [index, object] of objects.entries()) {
                const pieces = [`${index + 1} 0 obj\n`, ...object, '\nendobj\n'];
                offsets.push(size);
                parts.push(...pieces);
                size += pieces.reduce((total, piece) => total + piece.length, 0);
            }
            const entries = offsets.map(offset => `${String(offset).padStart(10, '0')} 00000 n \n`).join('');
            parts.push(`xref\n0 6\n0000000000 65535 f \n${entries}trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n${size}\n%%EOF\n`);
            return new Blob(parts, { type: 'application/pdf' });
        }

        const downloadButton = document.querySelector('#download-ticket');
        downloadButton.addEventListener('click', async () => {
            const ticket = document.querySelector('#ticket');
            const error = document.querySelector('#download-error');
            downloadButton.disabled = true;
            error.hidden = true;

            try {
                const width = ticket.offsetWidth;
                const height = ticket.offsetHeight;
                const markup = new XMLSerializer().serializeToString(ticket);
                const styles = document.querySelector('#ticket-styles').textContent;
                const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}">
                    <foreignObject width="100%" height="100%"><div xmlns="http://www.w3.org/1999/xhtml" style="color:#292017;font:16px system-ui,sans-serif">
                    <style><![CDATA[${styles}]]></style>${markup}</div></foreignObject></svg>`;
                const image = new Image();
                image.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
                await image.decode();

                const canvas = document.createElement('canvas');
                canvas.width = width * 2;
                canvas.height = height * 2;
                const context = canvas.getContext('2d');
                context.fillStyle = '#fffaf0';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                const url = URL.createObjectURL(ticketPdf(canvas));
                const link = document.createElement('a');
                link.href = url;
                link.download = `Yalla-Teer-${ticket.dataset.number}.pdf`;
                link.click();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
            } catch {
                error.hidden = false;
            } finally {
                downloadButton.disabled = false;
            }
        });
    </script>
</body>
</html>
