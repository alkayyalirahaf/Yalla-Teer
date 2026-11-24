@extends('airline-admin.flights.layout')

@section('title', 'Airline Bookings')

@section('flight-content')
<div class="flight-card">
    <div><h1>Bookings</h1><p>Bookings that include at least one of your airline's flights.</p></div>
</div>

<details class="panel section" open>
    <summary><strong>Search and filters</strong></summary>
    <form method="GET" action="{{ route('airline.bookings') }}" class="form-grid" style="margin-top:1rem">
        <label class="field">
            <span>Booking code</span>
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search booking code">
        </label>
        <label class="field">
            <span>Status</span>
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['pending', 'confirmed', 'cancelled', 'completed'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </label>
        <div class="actions" style="align-self:end">
            <button class="button" type="submit">Apply filters</button>
            <a class="button secondary" href="{{ route('airline.bookings') }}">Reset</a>
        </div>
    </form>
</details>

<section class="panel table-wrap section">
    <table>
        <caption>Airline bookings</caption>
        <thead>
            <tr>
                <th scope="col">Booking code</th>
                <th scope="col">Customer</th>
                <th scope="col">Flights</th>
                <th scope="col">Status</th>
                <th scope="col">Amount paid</th>
                <th scope="col">Created</th>
                <th scope="col">Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <th scope="row">{{ $booking->code }}</th>
                    <td>{{ $booking->customer?->user?->name ?? '—' }}</td>
                    <td>{{ $booking->airline_flights_count }}</td>
                    <td><span class="badge">{{ ucfirst($booking->status) }}</span></td>
                    <td>JOD {{ number_format((float) $booking->amount_paid, 2) }}</td>
                    <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
                    <td><a class="quiet" href="{{ route('airline.booking', $booking) }}">View booking</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No bookings found.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{ $bookings->links() }}
@endsection
