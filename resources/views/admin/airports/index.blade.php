@extends('super-admin.management-layout')

@section('admin-title', $title)

@section('admin-content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <h1 class="mb-0">{{ $title }}</h1>
    <a class="btn btn-primary px-4 py-2" href="{{ route('admin.airports.create') }}">Create airport</a>
</div>

<details class="card mb-3" open>
    <summary class="card-header fw-semibold">Search and filters</summary>
    <div class="card-body">
        <form action="{{ route('admin.airports') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="search">Search name or IATA code</label>
                <input class="form-control" id="search" name="search" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="city_id">City</label>
                <select class="form-select" id="city_id" name="city_id">
                    <option value="">All cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected((string) $filters['city_id'] === (string) $city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit">Apply filters</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.airports') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>
</details>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr><th scope="col">Name</th><th scope="col">IATA code</th><th scope="col">City</th><th scope="col">Actions</th></tr>
        </thead>
        <tbody>
            @forelse($airports as $airport)
                <tr>
                    <th scope="row">{{ $airport->name }}</th>
                    <td><span class="admin-flight-status">{{ $airport->code }}</span></td>
                    <td>{{ $airport->city->name }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.airports.edit', $airport) }}">Edit</a>
                            <form action="{{ route('admin.airports.destroy', $airport) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this airport?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="admin-flight-empty">No airports found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $airports->links('pagination::bootstrap-5') }}
@endsection
