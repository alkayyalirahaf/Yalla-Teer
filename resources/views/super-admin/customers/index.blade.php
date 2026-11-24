@extends('super-admin.management-layout')
@section('admin-title', 'Customers')
@section('admin-content')
    <h1>Customers</h1>
    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.customers') }}">
                <div class="col-md-8 col-lg-9">
                    <label class="form-label" for="customer-search">Search name or email</label>
                    <input id="customer-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name or email">
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit">Apply filters</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.customers') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </details>
    <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Bookings</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>@forelse($customers as $customer)<tr><td>{{ $customer->user->name }}</td><td>{{ $customer->user->email }}</td><td>{{ $customer->phone ?: '—' }}</td><td>{{ $customer->bookings_count }}</td><td>{{ $customer->is_banned ? 'Banned' : 'Active' }}</td><td><div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.customers.show', $customer) }}">Details</a></div></td></tr>
        @empty<tr><td colspan="6">No customers found.</td></tr>@endforelse</tbody></table></div>
    {{ $customers->links('super-admin.pagination') }}
@endsection