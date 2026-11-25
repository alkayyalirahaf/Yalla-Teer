@extends('super-admin.management-layout')
@section('admin-title', __('Airline admins'))
@section('admin-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><h1 class="mb-0">{{ __('Airline admins') }}</h1><a class="btn btn-primary px-4 py-2" href="{{ route('admin.airline-admins.create') }}">{{ __('Add admin') }}</a></div>
    <x-management-filters>
<form method="GET" action="{{ route('admin.airline-admins') }}" class="airline-admin-filters">
        <div><label class="form-label" for="search">{{ __('Search name or email') }}</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Name or email') }}"></div>
        <div><label class="form-label" for="airline_id">{{ __('Airline') }}</label><select class="form-select" id="airline_id" name="airline_id"><option value="">{{ __('All airlines') }}</option>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string) ($filters['airline_id'] ?? '') === (string) $airline->id)>{{ $airline->name }}</option>@endforeach</select></div>
        <div class="airline-admin-filter-actions"><button class="btn btn-primary">{{ __('Search / Filter') }}</button><a class="btn btn-outline-secondary" href="{{ route('admin.airline-admins') }}">{{ __('Clear') }}</a></div>
    </form>
</x-management-filters>
    <div class="table-responsive"><table class="table table-striped">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Airline') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Job title') }}</th><th>{{ __('Actions') }}</th></tr></thead>
        <tbody>@forelse($airlineAdmins as $admin)
            <tr><td>{{ $admin->user->name }}</td><td>{{ $admin->user->email }}</td><td>{{ $admin->airline->name }}</td><td>{{ $admin->phone ?: '—' }}</td><td>{{ $admin->job_title ?: '—' }}</td>
                <td>
                    <div class="airline-admin-table-actions">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.airline-admins.edit', $admin) }}">{{ __('Edit') }}</a>
                        <form class="d-inline" method="POST" action="{{ route('admin.airline-admins.destroy', $admin) }}" onsubmit="return confirm('Delete this admin?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty<tr><td colspan="6">{{ __('No airline admins found.') }}</td></tr>@endforelse</tbody>
    </table></div>{{ $airlineAdmins->links('super-admin.pagination') }}
@endsection
