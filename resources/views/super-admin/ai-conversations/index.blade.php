@extends('super-admin.management-layout')
@section('admin-title', __('AI conversations'))
@section('admin-content')
    <h1>{{ __('AI conversations') }}</h1>
    <x-management-filters>
<form method="GET" action="{{ route('admin.ai-conversations') }}" class="row g-2 mb-3">
        <div class="col-md-6"><label class="form-label" for="search">{{ __('Search title or customer') }}</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Conversation title or customer name') }}"></div>
        <div class="col-md-3"><label class="form-label" for="type">{{ __('Conversation type') }}</label><select class="form-select" id="type" name="type"><option value="">{{ __('All types') }}</option><option value="find" @selected(($filters['type'] ?? '') === 'find')>{{ __('Find') }}</option><option value="compare" @selected(($filters['type'] ?? '') === 'compare')>{{ __('Compare') }}</option><option value="tips" @selected(($filters['type'] ?? '') === 'tips')>{{ __('Tips') }}</option></select></div>
        <div class="col-md-3 d-flex align-items-end gap-2"><button class="btn btn-primary">{{ __('Search / Filter') }}</button><a class="btn btn-outline-secondary" href="{{ route('admin.ai-conversations') }}">{{ __('Clear') }}</a></div>
    </form>
</x-management-filters>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Date') }}</th><th></th></tr></thead><tbody>
        @forelse($conversations as $conversation)<tr><td>{{ $conversation->title }}</td><td>{{ __(ucfirst($conversation->type)) }}</td><td>{{ $conversation->customer?->user?->name ?? 'Guest' }}</td><td>{{ $conversation->created_at }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ai-conversations.show', $conversation) }}">{{ __('View') }}</a></td></tr>
        @empty<tr><td colspan="5">{{ __('No conversations found.') }}</td></tr>@endforelse
    </tbody></table></div>{{ $conversations->links('super-admin.pagination') }}
@endsection
