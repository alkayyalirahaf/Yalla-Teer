@extends('airline-admin.flights.layout')

@section('title', 'Flight '.$flight->flight_number)

@section('flight-content')
<div class="flight-card">
    <div>
        <h1>{{ $flight->flight_number }}</h1>
        <p><span class="badge">{{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}</span></p>
    </div>
    <div class="actions">
        <span class="badge">{{ __(ucfirst($flight->status)) }}</span>
        <a class="button" href="{{ route('airline.edit', $flight) }}">{{ __('Edit Flight') }}</a>
    </div>
</div>

<section class="panel cards section" aria-label="{{ __('Flight summary') }}">
    <div><p>{{ __('Origin') }}</p><h2>{{ $flight->originAirport->name }}</h2><p>{{ $flight->originAirport->city->name ?? '' }}</p></div>
    <div><p>{{ __('Destination') }}</p><h2>{{ $flight->destinationAirport->name }}</h2><p>{{ $flight->destinationAirport->city->name ?? '' }}</p></div>
    <div><p>{{ __('Departure') }}</p><h2>{{ $flight->departure_at->format('Y-m-d H:i') }}</h2></div>
    <div><p>{{ __('Arrival') }}</p><h2>{{ $flight->arrival_at->format('Y-m-d H:i') }}</h2></div>
</section>

@if($flight->trip_type === 'round_trip' && $flight->returnFlight)
<section class="panel section stack" aria-label="{{ __('Return flight') }}">
    <h2>{{ __('Return flight') }}</h2>
    <p><strong>{{ $flight->returnFlight->flight_number }}</strong> · {{ $flight->returnFlight->originAirport?->code }} → {{ $flight->returnFlight->destinationAirport?->code }}</p>
    <p>{{ __('Departure') }}: {{ $flight->returnFlight->departure_at?->format('Y-m-d H:i') }} · {{ __('Arrival') }}: {{ $flight->returnFlight->arrival_at?->format('Y-m-d H:i') }} · {{ __('Stops') }}: {{ $flight->returnFlight->stops }}</p>
</section>
@endif

<section class="section">
    <h2>{{ __('Cabin seat maps') }}</h2>
    <div class="cards">
        @forelse($flight->flightClasses as $flightClass)
            <article class="panel stack">
                <div class="flight-card">
                    <h3>{{ __(ucfirst($flightClass->class)) }}</h3>
                    <span class="badge">{{ $flightClass->total_seats }} seats</span>
                </div>
                <div class="actions" aria-label="{{ __('Seat status legend') }}">
                    <span class="badge" style="color:var(--gold);border-color:var(--gold)">{{ __('Available') }}</span>
                    <span class="badge" style="color:var(--highlight);border-color:var(--highlight)">{{ __('Held') }}</span>
                    <span class="badge" style="color:var(--muted);border-color:var(--border)">{{ __('Booked') }}</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <caption>{{ __(ucfirst($flightClass->class)) }} seat map</caption>
                        <thead><tr><th scope="col">{{ __('Row') }}</th><th scope="col">{{ __('Seats') }}</th></tr></thead>
                        <tbody>
                            @forelse($flightClass->seats->groupBy(fn ($seat) => (int) preg_replace('/\D+/', '', $seat->seat_number))->sortKeys() as $rowNumber => $seats)
                                <tr>
                                    <th scope="row">{{ $rowNumber }}</th>
                                    <td>
                                        <div class="actions">
                                            @foreach($seats as $seat)
                                                @php
                                                    $seatStyle = match ($seat->status) {
                                                        'available' => 'color:var(--gold);border-color:var(--gold)',
                                                        'held' => 'color:var(--highlight);border-color:var(--highlight)',
                                                        'booked' => 'color:var(--muted);border-color:var(--border)',
                                                        default => '',
                                                    };
                                                @endphp
                                                <span class="badge" style="{{ $seatStyle }}" aria-label="{{ $seat->seat_number }}: {{ $seat->status }}">{{ $seat->seat_number }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2">{{ __('No seats are configured for this cabin.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <p class="panel">{{ __('No cabin classes are configured for this flight.') }}</p>
        @endforelse
    </div>
</section>

<section class="panel table-wrap section">
    <h2>{{ __('Bookings') }}</h2>
    <table>
        <caption>{{ __('Passengers booked on this flight') }}</caption>
        <thead><tr><th scope="col">{{ __('Booking code') }}</th><th scope="col">{{ __('Customer') }}</th><th scope="col">{{ __('Class') }}</th><th scope="col">{{ __('Booking status') }}</th></tr></thead>
        <tbody>
            @forelse($flight->bookingFlights as $bookingFlight)
                <tr>
                    <th scope="row">{{ $bookingFlight->booking?->code ?? '—' }}</th>
                    <td>{{ $bookingFlight->booking?->customer?->user?->name ?? '—' }}</td>
                    <td>{{ $bookingFlight->flightClass?->class ? ucfirst($bookingFlight->flightClass->class) : '—' }}</td>
                    <td><span class="badge">{{ ucfirst($bookingFlight->booking?->status ?? 'unknown') }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('No bookings for this flight yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
