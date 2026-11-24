@extends('airline-admin.flights.layout')

@section('title', 'Booking '.$booking->code)

@section('flight-content')
<div class="flight-card">
    <div>
        <p class="eyebrow">{{ __('Booking details') }}</p>
        <h1>{{ $booking->code }}</h1>
    </div>
    <span class="badge">{{ __(ucfirst($booking->status)) }}</span>
</div>

@php
    $successfulPayment = $booking->payments->firstWhere('status', 'succeeded');
    $latestPayment = $booking->payments->sortByDesc('id')->first();
@endphp
<section class="cards section">
    <article class="panel">
        <p>{{ __('Customer') }}</p>
        <h2>{{ $booking->customer?->user?->name ?? '—' }}</h2>
        <p>{{ $booking->contact_email }}</p>
    </article>
    <article class="panel">
        <p>{{ __('Trip type') }}</p>
        <h2>{{ ucfirst(str_replace('_', ' ', $booking->trip_type)) }}</h2>
        <p>{{ __('Created') }} {{ $booking->created_at->format('Y-m-d H:i') }}</p>
    </article>
    <article class="panel">
        <p>{{ __('Payment status') }}</p>
        <h2>{{ $successfulPayment ? 'Paid' : ($latestPayment ? ucfirst($latestPayment->status) : 'Not paid') }}</h2>
        <p>JOD {{ number_format((float) $booking->amount_paid, 2) }} paid of JOD {{ number_format((float) $booking->total, 2) }}</p>
    </article>
</section>

<section class="panel table-wrap section">
    <h2>{{ __('Passengers') }}</h2>
    <table>
        <caption>{{ __('Passengers on this booking') }}</caption>
        <thead><tr><th scope="col">{{ __('Name') }}</th><th scope="col">{{ __('Type') }}</th><th scope="col">{{ __('Nationality') }}</th><th scope="col">{{ __('Email') }}</th></tr></thead>
        <tbody>
            @forelse($booking->passengers as $passenger)
                <tr>
                    <th scope="row">{{ trim($passenger->first_name.' '.$passenger->last_name) }}</th>
                    <td>{{ __(ucfirst($passenger->type)) }}</td>
                    <td>{{ $passenger->nationality ?: '—' }}</td>
                    <td>{{ $passenger->email ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('No passenger details are available.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<section class="section">
    <h2>{{ __('Flights and tickets') }}</h2>
    <div class="stack">
        @forelse($booking->bookingFlights as $bookingFlight)
            <article class="panel stack">
                <div class="flight-card">
                    <div>
                        <h3>{{ $bookingFlight->flight->flight_number }}</h3>
                        <p>{{ $bookingFlight->flight->originAirport->code }} → {{ $bookingFlight->flight->destinationAirport->code }}</p>
                    </div>
                    <div class="actions">
                        <span class="badge">{{ __(ucfirst($bookingFlight->direction)) }}</span>
                        <span class="badge">{{ __(ucfirst($bookingFlight->flightClass->class)) }}</span>
                        <a class="quiet" href="{{ route('airline.flight', $bookingFlight->flight) }}">{{ __('View flight') }}</a>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <caption>{{ __('Issued tickets') }}</caption>
                        <thead><tr><th scope="col">{{ __('Passenger') }}</th><th scope="col">{{ __('Ticket number') }}</th><th scope="col">{{ __('Seat') }}</th><th scope="col">{{ __('Status') }}</th></tr></thead>
                        <tbody>
                            @forelse($bookingFlight->tickets as $ticket)
                                <tr>
                                    <th scope="row">{{ trim($ticket->passenger->first_name.' '.$ticket->passenger->last_name) }}</th>
                                    <td>{{ $ticket->ticket_number }}</td>
                                    <td>{{ $ticket->seat?->seat_number ?? '—' }}</td>
                                    <td><span class="badge">{{ __(ucfirst($ticket->status)) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4">{{ __('No tickets have been issued for this flight.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <p class="panel">{{ __('No flights belonging to this airline are attached to the booking.') }}</p>
        @endforelse
    </div>
</section>

<section class="panel table-wrap section">
    <h2>{{ __('Payments') }}</h2>
    <table>
        <caption>{{ __('Payment records') }}</caption>
        <thead><tr><th scope="col">{{ __('Status') }}</th><th scope="col">{{ __('Amount') }}</th><th scope="col">{{ __('Receipt') }}</th><th scope="col">{{ __('Paid at') }}</th></tr></thead>
        <tbody>
            @forelse($booking->payments as $payment)
                <tr>
                    <th scope="row"><span class="badge">{{ __(ucfirst($payment->status)) }}</span></th>
                    <td>JOD {{ number_format((float) $payment->amount, 2) }}</td>
                    <td>{{ $payment->receipt_number ?? '—' }}</td>
                    <td>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('No payment record.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<div class="actions section">
    @if($booking->status !== 'cancelled')
        <form method="POST" action="{{ route('airline.bookings.cancel', $booking) }}" onsubmit="return confirm('Cancel this booking and release its seats?')">
            @csrf
            <button type="submit" class="button danger">{{ __('Cancel booking') }}</button>
        </form>
    @endif
    <a class="button secondary" href="{{ route('airline.bookings') }}">{{ __('Back to bookings') }}</a>
</div>
@endsection
