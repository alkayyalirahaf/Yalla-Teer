@extends('super-admin.airlines.layout')

@section('airlines-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h2 class="mb-0">Airline list</h2>
        <a class="btn btn-primary px-4 py-2" href="{{ route('admin.airlines.create') }}">Add airline</a>
    </div>

    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.airlines') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label" for="airline-search">Search name or code</label>
                    <input id="airline-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name or code">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="airline-status">Status</label>
                    <select id="airline-status" class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach (['pending', 'active', 'suspended'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit">Apply filters</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.airlines') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </details>

    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Logo</th><th>Name</th><th>Code</th><th>Country</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse ($airlines as $airline)
            <tr>
                <td>@if ($airline->logo)<img src="{{ asset('storage/'.$airline->logo) }}" alt="{{ $airline->name }} logo" width="48">@else — @endif</td>
                <td>{{ $airline->name }}</td><td>{{ $airline->code }}</td><td>{{ $airline->country }}</td><td>{{ ucfirst($airline->status) }}</td>
                <td>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.airlines.edit', $airline->id) }}">Edit</a>
                    @if ($airline->status !== 'suspended')
                        <form class="d-inline" method="POST" action="{{ route('admin.airlines.suspend', $airline->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">Suspend</button></form>
                    @endif
                    <form class="d-inline" method="POST" action="{{ route('admin.airlines.destroy', $airline->id) }}" onsubmit="return confirm('Delete this airline?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">No airlines found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $airlines->links('super-admin.pagination') }}
@endsection
