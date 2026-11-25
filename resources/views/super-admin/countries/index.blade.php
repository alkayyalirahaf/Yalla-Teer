@extends('super-admin.countries.layout')

@section('countries-content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h1 class="mb-0">{{ __('Countries') }}</h1>
        <a class="btn btn-primary px-4 py-2" href="{{ route('admin.countries.create') }}">{{ __('Add country') }}</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr><th>{{ __('Name') }}</th><th>{{ __('Code') }}</th><th>{{ __('Currency') }}</th><th>{{ __('Language') }}</th><th>{{ __('Local apps') }}</th><th>{{ __('Actions') }}</th></tr>
            </thead>
            <tbody>
                @forelse($countries as $country)
                    <tr>
                        <td>{{ $country->name }}</td>
                        <td>{{ $country->code }}</td>
                        <td>{{ $country->currency }}</td>
                        <td>{{ $country->language }}</td>
                        <td class="text-break">{{ $country->local_apps ?: '—' }}</td>
                        <td>
                            <div class="country-actions">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.countries.edit', $country) }}">{{ __('Edit') }}</a>
                            @if($country->cities_count === 0)
                                <form class="d-inline" method="POST" action="{{ route('admin.countries.destroy', $country) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Delete') }}</button>
                                </form>
                            @else
                                <small class="country-delete-note">
                                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                    {{ __('Deletion locked · linked cities') }}
                                </small>
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('No countries found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
        {{ $countries->links('super-admin.pagination') }}
@endsection
