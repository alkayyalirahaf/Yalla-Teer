@extends('super-admin.airlines.layout')

@section('airlines-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h2 class="mb-0">{{ __('Airline list') }}</h2>
        <a class="btn btn-primary px-4 py-2" href="{{ route('admin.airlines.create') }}">{{ __('Add airline') }}</a>
    </div>

    <x-management-filters>
<form method="GET" action="{{ route('admin.airlines') }}" class="row g-2 mb-3 align-items-end">
        <div class="col-md-6"><input aria-label="{{ __('Search name or code') }}" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or code') }}"></div>
        <div class="col-md-3">
            <select aria-label="{{ __('Status') }}" class="form-select" name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['pending', 'active', 'suspended'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-primary">{{ __('Filter') }}</button></div>
        <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('admin.airlines') }}">{{ __('Clear') }}</a></div>
    </form>
</x-management-filters>

    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>{{ __('Logo') }}</th><th>{{ __('Name') }}</th><th>{{ __('Code') }}</th><th>{{ __('Country') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
        <tbody>
        @forelse ($airlines as $airline)
            <tr>
                <td>@if ($airline->logo)<img src="{{ asset('storage/'.$airline->logo) }}" alt="{{ $airline->name }} logo" width="48">@else — @endif</td>
                <td>{{ $airline->name }}</td><td>{{ $airline->code }}</td><td>{{ $airline->country }}</td><td>{{ __(ucfirst($airline->status)) }}</td>
                <td>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.airlines.edit', $airline->id) }}">{{ __('Edit') }}</a>
                    @if ($airline->status !== 'suspended')
                        <form class="d-inline" method="POST" action="{{ route('admin.airlines.suspend', $airline->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">{{ __('Suspend') }}</button></form>
                    @endif
                    <form class="d-inline" method="POST" action="{{ route('admin.airlines.destroy', $airline->id) }}" onsubmit="return confirm('Delete this airline?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button></form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">{{ __('No airlines found.') }}</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $airlines->links('super-admin.pagination') }}
@endsection
