@extends('super-admin.management-layout')

@section('admin-title', 'Flights')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h1 class="mb-0">Flights</h1>
        <span class="text-white-50">{{ number_format($flights->total()) }} {{ $flights->total() === 1 ? 'flight' : 'flights' }}</span>
    </div>

    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.flights') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="search">Flight number</label>
                    <input class="form-control" id="search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="e.g. TK102">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach(['scheduled', 'delayed', 'cancelled', 'completed'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="airline_id">Airline</label>
                    <select class="form-select" id="airline_id" name="airline_id">
                        <option value="">All airlines</option>
                        @foreach($airlines as $airline)
                            <option value="{{ $airline->id }}" @selected((string) ($filters['airline_id'] ?? '') === (string) $airline->id)>
                                {{ $airline->name }} ({{ $airline->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit">Apply filters</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.flights') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </details>

        <div class="table-responsive" role="region" aria-label="Flight results" tabindex="0">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">Flight number</th>
                        <th scope="col">Airline</th>
                        <th scope="col">Route</th>
                        <th scope="col">Departure</th>
                        <th scope="col">Arrival</th>
                        <th scope="col">Stops</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flights as $flight)
                        <tr>
                            <th scope="row">{{ $flight->flight_number }}</th>
                            <td>{{ $flight->airline->name }} <small>{{ $flight->airline->code }}</small></td>
                            <td class="admin-flight-route">{{ $flight->originAirport->code }} <span>&rarr;</span> {{ $flight->destinationAirport->code }}</td>
                            <td><strong>{{ $flight->departure_at->format('H:i') }}</strong><small>{{ $flight->departure_at->format('d M Y') }}</small></td>
                            <td><strong>{{ $flight->arrival_at->format('H:i') }}</strong><small>{{ $flight->arrival_at->format('d M Y') }}</small></td>
                            <td>{{ $flight->stops ?: 'Nonstop' }}</td>
                            <td><span @class(['badge', 'text-bg-success' => $flight->status === 'completed', 'text-bg-danger' => $flight->status === 'cancelled', 'text-bg-warning' => $flight->status === 'delayed', 'text-bg-secondary' => $flight->status === 'scheduled'])>{{ ucfirst($flight->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No flights found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $flights->links('pagination::bootstrap-5') }}
@endsection
