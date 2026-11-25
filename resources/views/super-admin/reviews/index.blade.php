@extends('super-admin.management-layout')
@section('admin-title', __('Review moderation'))
@section('admin-content')
    <h1>{{ __('Review moderation') }}</h1>
    <x-management-filters>
<form method="GET" action="{{ route('admin.reviews') }}" class="row g-2 mb-3">
        <div class="col-md-6"><label class="form-label" for="search">{{ __('Search customer or flight') }}</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Customer name or flight number') }}"></div>
        <div class="col-md-3"><label class="form-label" for="status">{{ __('Status') }}</label><select class="form-select" id="status" name="status"><option value="">{{ __('All statuses') }}</option><option value="visible" @selected(($filters['status'] ?? '') === 'visible')>{{ __('Visible') }}</option><option value="hidden" @selected(($filters['status'] ?? '') === 'hidden')>{{ __('Hidden') }}</option></select></div>
        <div class="col-md-3 d-flex align-items-end gap-2"><button class="btn btn-primary">{{ __('Search / Filter') }}</button><a class="btn btn-outline-secondary" href="{{ route('admin.reviews') }}">{{ __('Clear') }}</a></div>
    </form>
</x-management-filters>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Flight') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Status') }}</th><th>{{ __('Save') }}</th></tr></thead><tbody>
        @forelse($reviews as $review)<tr><td>{{ $review->customer->user->name }}</td><td>{{ $review->bookingFlight->flight->flight_number }}</td><td>{{ $review->rating }}/5</td><td>{{ $review->comment ?: '—' }}</td>
            <td colspan="2"><form class="d-flex gap-2" method="POST" action="{{ route('admin.reviews.update', $review) }}">@csrf @method('PATCH')<select class="form-select" name="status"><option value="visible" @selected($review->status === 'visible')>{{ __('Visible') }}</option><option value="hidden" @selected($review->status === 'hidden')>{{ __('Hidden') }}</option></select><button class="btn btn-primary">{{ __('Save') }}</button></form></td></tr>
        @empty<tr><td colspan="6">{{ __('No reviews found.') }}</td></tr>@endforelse
    </tbody></table></div>{{ $reviews->links('super-admin.pagination') }}
@endsection
