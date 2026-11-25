@extends('super-admin.management-layout')
@section('admin-title', __('Customers'))
@section('admin-content')
    <h1>{{ __('Customers') }}</h1>
    <x-management-filters>
<form class="mb-3 d-flex gap-2 align-items-end" method="GET" action="{{ route('admin.customers') }}"><input aria-label="{{ __('Search name or email') }}" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or email') }}"><button class="btn btn-primary" type="submit">{{ __('Search') }}</button><a class="button secondary" href="{{ route('admin.customers') }}">{{ __('Reset') }}</a>
</form>
</x-management-filters>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Bookings') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>@forelse($customers as $customer)<tr><td>{{ $customer->user->name }}</td><td>{{ $customer->user->email }}</td><td>{{ $customer->phone ?: '—' }}</td><td>{{ $customer->bookings_count }}</td><td>{{ $customer->is_banned ? 'Banned' : 'Active' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.customers.show', $customer) }}">{{ __('Details') }}</a></td></tr>
        @empty<tr><td colspan="6">{{ __('No customers found.') }}</td></tr>@endforelse</tbody></table></div>
    {{ $customers->links('super-admin.pagination') }}
@endsection
