@extends('airline-admin.flights.layout')

@section('title', __('Flights'))

@section('flight-content')
<div class="flight-card">
    <div><h1>{{ __('Flights') }}</h1><p>{{ __('Manage your airline\'s schedule and seat inventory.') }}</p></div>
    <a class="button" href="{{ route('airline.create') }}">{{ __('Create Flight') }}</a>
</div>

<x-management-filters>
<form method="GET" action="{{ route('airline.flights') }}" class="panel form-grid section">
    <label class="field">
        <span>{{ __('Flight number') }}</span>
        <input aria-label="{{ __('Search flight number') }}" type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search flight number') }}">
    </label>
    <label class="field">
        <span>{{ __('Status') }}</span>
        <select aria-label="{{ __('Status') }}" name="status">
            <option value="">{{ __('All statuses') }}</option>
            @foreach(['scheduled', 'delayed', 'cancelled', 'completed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>
            @endforeach
        </select>
    </label>
    <div class="actions">
        <button class="button" type="submit">{{ __('Filter') }}</button>
        <a class="button secondary" href="{{ route('airline.flights') }}">{{ __('Clear') }}</a>
    </div>
</form>
</x-management-filters>

<section class="panel table-wrap section">
    <table>
        <caption>{{ __('Flight schedule') }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('Flight') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Route') }}</th>
                <th scope="col">{{ __('Departure') }}</th>
                <th scope="col">{{ __('Arrival') }}</th>
                <th scope="col">{{ __('Stops') }}</th>
                <th scope="col">{{ __('Seats') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($flights as $flight)
                <tr>
                    <th scope="row">                         {{ $flight->flight_number }}                         @if($flight->trip_type === 'round_trip' && $flight->returnFlight)                             <div class="muted">{{ __('Return') }}: {{ $flight->returnFlight->flight_number }}</div>                         @endif                     </th>                     <td>{{ $flight->trip_type === 'round_trip' ? __('Round trip') : __('One way') }}</td>                                         <td>{{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}</td>
                    <td>{{ $flight->departure_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $flight->arrival_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $flight->stops }}</td>
                    <td>{{ $flight->flightClasses->sum('total_seats') }}</td>
                    <td><span class="badge">{{ __(ucfirst($flight->status)) }}</span></td>
                    <td>
                        <div class="actions">
                            <a class="button secondary" href="{{ route('airline.flight', $flight) }}">{{ __('View') }}</a>
                            <a class="button secondary" href="{{ route('airline.edit', $flight) }}">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('airline.destroy', $flight) }}" onsubmit="return confirm('Delete this flight?')">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">{{ __('No flights found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{ $flights->links('super-admin.pagination') }}
@endsection
