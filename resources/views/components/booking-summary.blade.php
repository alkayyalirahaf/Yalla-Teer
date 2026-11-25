@props(['code' => 'YTDEMO'])
<section class="panel stack">
    <h2>{{ $code }}</h2>
    <p><span class="badge">{{ __('Sample booking') }}</span></p>
    <div><h3>{{ __('Itinerary') }}</h3><p>YT 402 · Amman → Dubai · 08:10</p></div>
    <div><h3>{{ __('Passengers') }}</h3><p>{{ __('Sample Traveler') }}</p></div>
    <div><h3>{{ __('Tickets') }}</h3><p>{{ __('Ticket downloads and QR codes will appear when ticketing is connected.') }}</p></div>
</section>
