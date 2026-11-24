@extends('super-admin.management-layout')
@section('admin-title', 'Airline admins')
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><h1 class="mb-0">Airline admins</h1><a class="btn btn-primary px-4 py-2" href="{{ route('admin.airline-admins.create') }}">Add admin</a></div>
    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.airline-admins') }}" class="row g-3 align-items-end">
                <div class="col-md-5"><label class="form-label" for="search">Search name or email</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email"></div>
                <div class="col-md-4"><label class="form-label" for="airline_id">Airline</label><select class="form-select" id="airline_id" name="airline_id"><option value="">All airlines</option>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string) ($filters['airline_id'] ?? '') === (string) $airline->id)>{{ $airline->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit">Apply filters</button><a class="btn btn-outline-secondary" href="{{ route('admin.airline-admins') }}">Reset</a></div></div>
            </form>
        </div>
    </details>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Name</th><th>Email</th><th>Airline</th><th>Phone</th><th>Job title</th><th>Actions</th></tr></thead>
        <tbody>@forelse($airlineAdmins as $admin)
            <tr><td>{{ $admin->user->name }}</td><td>{{ $admin->user->email }}</td><td>{{ $admin->airline->name }}</td><td>{{ $admin->phone ?: '—' }}</td><td>{{ $admin->job_title ?: '—' }}</td>
                <td>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.airline-admins.edit', $admin) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('admin.airline-admins.destroy', $admin) }}" onsubmit="return confirm('Delete this admin?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty<tr><td colspan="6">No airline admins found.</td></tr>@endforelse</tbody>
    </table></div>{{ $airlineAdmins->links('super-admin.pagination') }}
@endsection