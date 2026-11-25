@extends('super-admin.management-layout')
@section('admin-title', __('Cities'))

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h1 class="mb-0">{{ __('Cities') }}</h1>
        <a class="btn btn-primary px-4 py-2" href="{{ route('admin.cities.create') }}">{{ __('Add city') }}</a>
    </div>
    @error('city')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>{{ __('City') }}</th><th>{{ __('Country') }}</th><th>{{ __('Best season') }}</th><th>{{ __('Airports') }}</th><th>{{ __('Actions') }}</th></tr></thead>
            <tbody>
                @forelse($cities as $city)
                    <tr>
                        <th scope="row">{{ $city->name }}</th>
                        <td>{{ $city->country->name }}</td>
                        <td>{{ $city->best_season ?: '—' }}</td>
                        <td>{{ $city->airports_count }}</td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.cities.edit', $city) }}">{{ __('Edit') }}</a>
                            @if(!$city->airports_count)
                                <form class="d-inline" method="POST" action="{{ route('admin.cities.destroy', $city) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">{{ __('No cities added yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $cities->links('super-admin.pagination') }}
@endsection
