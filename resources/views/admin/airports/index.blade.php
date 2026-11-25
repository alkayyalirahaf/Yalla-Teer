@extends('super-admin.management-layout')

@section('admin-title', $title)

@section('admin-content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <h1 class="mb-0">{{ __($title) }}</h1>
    <a class="btn btn-primary px-4 py-2" href="{{ route('admin.airports.create') }}">{{ __('Create airport') }}</a>
</div>

<x-management-filters>
<form action="{{ route('admin.airports') }}" method="GET" class="panel p-3 mb-3 row g-3 align-items-end">
    <div class="col-md-5">
        <label class="form-label" for="search">{{ __('Search name or IATA code') }}</label>
        <input class="form-control" id="search" name="search" value="{{ $filters['search'] }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="city_id">{{ __('City') }}</label>
        <select class="form-select" id="city_id" name="city_id">
            <option value="">{{ __('All cities') }}</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}" @selected((string) $filters['city_id'] === (string) $city->id)>{{ $city->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary" type="submit">{{ __('Filter') }}</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.airports') }}">{{ __('Clear') }}</a>
    </div>
</form>
</x-management-filters>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr><th scope="col">{{ __('Name') }}</th><th scope="col">{{ __('IATA code') }}</th><th scope="col">{{ __('City') }}</th><th scope="col">{{ __('Actions') }}</th></tr>
        </thead>
        <tbody>
            @forelse($airports as $airport)
                <tr>
                    <th scope="row">{{ $airport->name }}</th>
                    <td><span class="admin-flight-status">{{ $airport->code }}</span></td>
                    <td>{{ $airport->city->name }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.airports.edit', $airport) }}">{{ __('Edit') }}</a>
                            <form action="{{ route('admin.airports.destroy', $airport) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this airport?')">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="admin-flight-empty">{{ __('No airports found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $airports->links('super-admin.pagination') }}
@endsection
