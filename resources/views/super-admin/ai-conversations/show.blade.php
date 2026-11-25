@extends('super-admin.management-layout')
@section('admin-title', __('AI conversation details'))
@section('admin-content')
    <a class="btn btn-outline-secondary mb-3" href="{{ route('admin.ai-conversations') }}">{{ __('← Back to conversations') }}</a>
    <h1>{{ $conversation->title }}</h1>
    <div class="panel p-3 p-md-4 mb-4">
        <p class="mb-2"><strong>{{ __('Type:') }}</strong> {{ __(ucfirst($conversation->type)) }}</p>
        <p class="mb-2"><strong>{{ __('Customer:') }}</strong> {{ $conversation->customer?->user?->name ?? 'Guest' }}</p>
        <p class="mb-0"><strong>{{ __('Created:') }}</strong> {{ $conversation->created_at?->format('Y-m-d H:i') }}</p>
    </div>
    <h2>{{ __('Recommendations') }}</h2>
    <div class="panel p-3 p-md-4">
        <ul class="mb-0">@forelse($conversation->recommendations as $recommendation)<li>{{ $recommendation->flight?->airline?->name }} {{ $recommendation->flight?->flight_number }} — {{ $recommendation->reason }}</li>@empty<li>{{ __('No recommendations recorded.') }}</li>@endforelse</ul>
    </div>
@endsection
