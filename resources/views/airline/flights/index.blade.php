@extends('airline-admin.flights.layout')

@section('title', 'Flights')

@section('flight-content')
<div class="flight-card">
    <div><h1>Flights</h1><p>Manage your airline's schedule and seat inventory.</p></div>
    <a class="button" href="{{ route('airline.create') }}">Create Flight</a>
</div>

<details class="panel section" open>
    <summary><strong>Search and filters</strong></summary>
    <form method="GET" action="{{ route('airline.flights') }}" class="form-grid" style="margin-top:1rem">
        <label class="field">
            <span>Flight number</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search flight number">
        </label>
        <label class="field">
            <span>Status</span>
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['scheduled', 'delayed', 'cancelled', 'completed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </label>
        <div class="actions" style="align-self:end">
            <button class="button" type="submit">Apply filters</button>
            <a class="button secondary" href="{{ route('airline.flights') }}">Reset</a>
        </div>
    </form>
</details>

<section class="panel table-wrap section">
    <table>
        <caption>Flight schedule</caption>
        <thead>
            <tr>
                <th scope="col">Flight</th>
                <th scope="col">Route</th>
                <th scope="col">Departure</th>
                <th scope="col">Arrival</th>
                <th scope="col">Stops</th>
                <th scope="col">Seats</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($flights as $flight)
                <tr>
                    <th scope="row">{{ $flight->flight_number }}</th>
                    <td>{{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}</td>
                    <td>{{ $flight->departure_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $flight->arrival_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $flight->stops }}</td>
                    <td>{{ $flight->flightClasses->sum('total_seats') }}</td>
                    <td><span class="badge">{{ ucfirst($flight->status) }}</span></td>
                    <td>
                        <div class="actions">
                            <a class="button secondary" href="{{ route('airline.flight', $flight) }}">View</a>
                            <a class="button secondary" href="{{ route('airline.edit', $flight) }}">Edit</a>
                            <form method="POST" action="{{ route('airline.destroy', $flight) }}" onsubmit="return confirm('Delete this flight?')">
                                @csrf
                                @method('DELETE')
                                <button class="quiet" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No flights found.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{ $flights->links() }}
@endsection
