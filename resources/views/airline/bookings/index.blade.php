@extends('airline-admin.flights.layout')

@section('title', __('Airline Bookings'))

@section('flight-content')
<div class="flight-card">
    <div><h1>{{ __('Bookings') }}</h1><p>{{ __('Bookings that include at least one of your airline\'s flights.') }}</p></div>
</div>

<x-management-filters>
<form method="GET" action="{{ route('airline.bookings') }}" class="panel form-grid section">
    <label class="field">
        <span>{{ __('Booking code') }}</span>
        <input aria-label="{{ __('Search booking code') }}" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search booking code') }}">
    </label>
    <label class="field">
        <span>{{ __('Status') }}</span>
        <select aria-label="{{ __('Status') }}" name="status">
            <option value="">{{ __('All statuses') }}</option>
            @foreach(['pending', 'confirmed', 'cancelled', 'completed'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(ucfirst($status)) }}</option>
            @endforeach
        </select>
    </label>
    <div class="actions">
        <button class="button" type="submit">{{ __('Filter') }}</button>
        <a class="button secondary" href="{{ route('airline.bookings') }}">{{ __('Clear') }}</a>
    </div>
</form>
</x-management-filters>

<section class="panel table-wrap section">
    <table>
        <caption>{{ __('Airline bookings') }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('Booking code') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Flights') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Amount paid') }}</th>
                <th scope="col">{{ __('Created') }}</th>
                <th scope="col">{{ __('Details') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <th scope="row">{{ $booking->code }}</th>
                    <td>{{ $booking->customer?->user?->name ?? '—' }}</td>
                    <td>{{ $booking->airline_flights_count }}</td>
                    <td><span class="badge">{{ __(ucfirst($booking->status)) }}</span></td>
                    <td>JOD {{ number_format((float) $booking->amount_paid, 2) }}</td>
                    <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
                    <td><a class="quiet" href="{{ route('airline.booking', $booking) }}">{{ __('View booking') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="7">{{ __('No bookings found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{ $bookings->links('super-admin.pagination') }}
@endsection
