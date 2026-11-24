@extends('super-admin.management-layout')
@section('admin-title', 'Review moderation')
@section('admin-content')
    <h1>Review moderation</h1>
    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reviews') }}" class="row g-3 align-items-end">
                <div class="col-md-6"><label class="form-label" for="search">Search customer or flight</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Customer name or flight number"></div>
                <div class="col-md-3"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All statuses</option><option value="visible" @selected(($filters['status'] ?? '') === 'visible')>Visible</option><option value="hidden" @selected(($filters['status'] ?? '') === 'hidden')>Hidden</option></select></div>
                <div class="col-md-3"><div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit">Apply filters</button><a class="btn btn-outline-secondary" href="{{ route('admin.reviews') }}">Reset</a></div></div>
            </form>
        </div>
    </details>
    <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Customer</th><th>Flight</th><th>Rating</th><th>Comment</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($reviews as $review)<tr><td>{{ $review->customer->user->name }}</td><td>{{ $review->bookingFlight->flight->flight_number }}</td><td>{{ $review->rating }}/5</td><td>{{ $review->comment ?: '—' }}</td>
            <td colspan="2"><form class="d-flex flex-wrap gap-2 align-items-center" method="POST" action="{{ route('admin.reviews.update', $review) }}">@csrf @method('PATCH')<select class="form-select w-auto" name="status"><option value="visible" @selected($review->status === 'visible')>Visible</option><option value="hidden" @selected($review->status === 'hidden')>Hidden</option></select><button class="btn btn-primary">Save</button></form></td></tr>
        @empty<tr><td colspan="6">No reviews found.</td></tr>@endforelse
    </tbody></table></div>{{ $reviews->links('super-admin.pagination') }}
@endsection