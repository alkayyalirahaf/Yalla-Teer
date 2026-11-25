@extends('super-admin.management-layout')

@section('admin-title', __('Flights'))

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h1 class="mb-0">{{ __('Flights') }}</h1>
        <span class="text-white-50">{{ number_format($flights->total()) }} {{ $flights->total() === 1 ? 'flight' : 'flights' }}</span>
    </div>

    <x-management-filters>
<form method="GET" action="{{ route('admin.flights') }}" class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="search">{{ __('Flight number') }}</label>
                <input class="form-control" id="search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('e.g. TK102') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select class="form-select" id="status" name="status">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach(['scheduled', 'delayed', 'cancelled', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="airline_id">{{ __('Airline') }}</label>
                <select class="form-select" id="airline_id" name="airline_id">
                    <option value="">{{ __('All airlines') }}</option>
                    @foreach($airlines as $airline)
                        <option value="{{ $airline->id }}" @selected((string) ($filters['airline_id'] ?? '') === (string) $airline->id)>
                            {{ $airline->name }} ({{ $airline->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary" type="submit">{{ __('Filter') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.flights') }}">{{ __('Clear') }}</a>
            </div>
    </form>
</x-management-filters>

        <div class="table-responsive" role="region" aria-label="{{ __('Flight results') }}" tabindex="0">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Flight number') }}</th>
                        <th scope="col">{{ __('Airline') }}</th>
                        <th scope="col">{{ __('Route') }}</th>
                        <th scope="col">{{ __('Departure') }}</th>
                        <th scope="col">{{ __('Arrival') }}</th>
                        <th scope="col">{{ __('Stops') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
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
                            <td><span @class(['badge', 'text-bg-success' => $flight->status === 'completed', 'text-bg-danger' => $flight->status === 'cancelled', 'text-bg-warning' => $flight->status === 'delayed', 'text-bg-secondary' => $flight->status === 'scheduled'])>{{ __(ucfirst($flight->status)) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">{{ __('No flights found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $flights->links('super-admin.pagination') }}
@endsection
