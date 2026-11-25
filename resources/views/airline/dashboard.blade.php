@extends('airline-admin.flights.layout')

@section('title', __('Airline Dashboard'))

@section('flight-content')
<div class="admin-dashboard">
<header class="airline-dashboard-heading">
    <h1>{{ __('Dashboard') }}</h1>
    <a class="button" href="{{ route('airline.create') }}">{{ __('Create flight') }}</a>
</header>

<section class="airline-metrics" aria-label="{{ __('Airline performance') }}">
    <article class="panel"><p>{{ __('Total flights') }}</p><h2>{{ number_format($totalFlights) }}</h2></article>
    <article class="panel"><p>{{ __('Upcoming scheduled flights') }}</p><h2>{{ number_format($upcomingFlightsCount) }}</h2></article>
    <article class="panel"><p>{{ __('Total bookings') }}</p><h2>{{ number_format($totalBookings) }}</h2></article>
    <article class="panel"><p>{{ __('Total revenue') }}</p><h2>JOD {{ number_format((float) $totalRevenue, 2) }}</h2></article>
    <article class="panel"><p>{{ __('Seat occupancy') }}</p><h2>{{ number_format((float) $seatOccupancyRate, 1) }}%</h2></article>
    <article class="panel"><p>{{ __('Average rating') }}</p><h2>{{ $averageRating === null ? '—' : number_format((float) $averageRating, 1).' / 5' }}</h2></article>
</section>

<section class="panel table-wrap section" id="upcoming-flights">
    <div class="airline-dashboard-section-heading"><h2>{{ __('Upcoming flights') }}</h2><a class="button secondary" href="{{ route('airline.flights') }}">{{ __('All flights') }}</a></div>
    <table>
        <caption class="airline-table-caption">{{ __('Upcoming flights') }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('Flight') }}</th>
                <th scope="col">{{ __('Route') }}</th>
                <th scope="col">{{ __('Departure') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Available seats') }}</th>
                <th scope="col">{{ __('Details') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($upcomingFlights as $flight)
                <tr>
                    <th scope="row">{{ $flight->flight_number }}</th>
                    <td>{{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}</td>
                    <td>{{ $flight->departure_at->format('Y-m-d H:i') }}</td>
                    <td><span class="badge">{{ __(ucfirst($flight->status)) }}</span></td>
                    <td>{{ $flight->flightClasses->sum(fn ($flightClass) => $flightClass->seats->count()) }}</td>
                    <td><a class="quiet" href="{{ route('airline.flight', $flight) }}">{{ __('View flight') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('No upcoming flights.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $upcomingFlights->links('super-admin.pagination') }}
</section>

<section class="cards section">
    <article class="panel table-wrap" id="recent-bookings">
        <div class="flight-card"><h2>{{ __('Recent bookings') }}</h2><a class="button secondary" href="{{ route('airline.bookings') }}">{{ __('All bookings') }}</a></div>
        <table>
            <caption>{{ __('Latest bookings on your flights') }}</caption>
            <thead><tr><th scope="col">{{ __('Booking') }}</th><th scope="col">{{ __('Customer') }}</th><th scope="col">{{ __('Status') }}</th></tr></thead>
            <tbody>
                @forelse($recentBookings as $booking)
                    <tr>
                        <th scope="row"><a href="{{ route('airline.booking', $booking) }}">{{ $booking->code }}</a></th>
                        <td>{{ $booking->customer?->user?->name ?? '—' }}</td>
                        <td><span class="badge">{{ __(ucfirst($booking->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3">{{ __('No recent bookings.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $recentBookings->links('super-admin.pagination') }}
    </article>

    <article class="panel table-wrap" id="recent-reviews">
        <div class="flight-card"><h2>{{ __('Recent reviews') }}</h2><a class="button secondary" href="{{ route('airline.reviews') }}">{{ __('All reviews') }}</a></div>
        <table>
            <caption>{{ __('Latest reviews for your flights') }}</caption>
            <thead><tr><th scope="col">{{ __('Flight') }}</th><th scope="col">{{ __('Rating') }}</th><th scope="col">{{ __('Review') }}</th></tr></thead>
            <tbody>
                @forelse($recentReviews as $review)
                    <tr>
                        <th scope="row">{{ $review->bookingFlight?->flight?->flight_number ?? '—' }}</th>
                        <td>{{ $review->rating }} / 5</td>
                        <td>{{ \Illuminate\Support\Str::limit($review->comment ?? '', 90) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">{{ __('No recent reviews.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $recentReviews->links('super-admin.pagination') }}
    </article>
</section>
</div>
@endsection
