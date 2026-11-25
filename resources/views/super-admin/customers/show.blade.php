@extends('super-admin.management-layout')
@section('admin-title', __('Customer details'))
@section('admin-content')
    <h1>{{ $customer->user->name }}</h1>
    <p>{{ __('Email:') }} {{ $customer->user->email }} | Phone: {{ $customer->phone ?: '—' }} | Balance: {{ $customer->credit_balance }}</p>
    <p>Status: {{ $customer->is_banned ? 'Banned' : 'Active' }}</p>
    <form class="mb-4" method="POST" action="{{ route('admin.customers.ban', $customer) }}">@csrf @method('PATCH')
        <input type="hidden" name="is_banned" value="{{ $customer->is_banned ? 0 : 1 }}">
        <button class="btn {{ $customer->is_banned ? 'btn-success' : 'btn-danger' }}">{{ $customer->is_banned ? 'Unban customer' : 'Ban customer' }}</button>
    </form>
    <h2>{{ __('Bookings') }}</h2>
    <div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Code') }}</th><th>{{ __('Status') }}</th><th>{{ __('Total') }}</th><th>{{ __('Date') }}</th></tr></thead><tbody>
        @forelse($bookings as $booking)<tr><td>{{ $booking->code }}</td><td>{{ $booking->status }}</td><td>{{ $booking->total }}</td><td>{{ $booking->created_at }}</td></tr>
        @empty<tr><td colspan="4">{{ __('No bookings.') }}</td></tr>@endforelse
    </tbody></table></div>{{ $bookings->links('super-admin.pagination') }}
    <a class="btn btn-outline-secondary mt-3" href="{{ route('admin.customers') }}">{{ __('Back to customers') }}</a>
@endsection
