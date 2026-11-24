@extends('super-admin.management-layout')
@section('admin-title', 'Cities')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h1 class="mb-1">Cities</h1>
            <p class="text-muted mb-0">Cities are part of each destination. A destination has cities and airports.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary px-4 py-2" href="{{ route('admin.countries') }}">View destinations</a>
            <a class="btn btn-primary px-4 py-2" href="{{ route('admin.cities.create') }}">Add city</a>
        </div>
    </div>
    @error('city')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>City</th><th>Destination</th><th>Best season</th><th>Airports</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($cities as $city)
                    <tr>
                        <th scope="row">{{ $city->name }}</th>
                        <td>{{ $city->country->name }}</td>
                        <td>{{ $city->best_season ?: '—' }}</td>
                        <td>{{ $city->airports_count }}</td>
                        <td class="text-nowrap">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.cities.edit', $city) }}">Edit</a>
                                @if(!$city->airports_count)
                                    <form class="d-inline" method="POST" action="{{ route('admin.cities.destroy', $city) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No cities added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $cities->links('pagination::bootstrap-5') }}
@endsection
