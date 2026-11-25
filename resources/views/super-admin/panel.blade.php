@extends('super-admin.management-layout')

@section('admin-title', $title)
@section('admin-content')
    @if(($screen ?? '') === 'dashboard')
        @include('super-admin.dashboard.content')
    @elseif(($screen ?? '') === 'credits')
        <h1>{{ __($title) }}</h1>
        @include('super-admin.credits.content')
    @else
    <div class="admin-page-heading">
        <p class="eyebrow mb-2">{{ __('Platform administration') }}</p>
        <h1>{{ __($title) }}</h1>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>@foreach($columns as $column)<th scope="col">{{ $column }}</th>@endforeach</tr>
            </thead>
            <tbody>
                <tr><td colspan="{{ max(count($columns), 1) }}" class="text-center py-5">
                    <strong class="d-block mb-1">{{ __('No records to display') }}</strong>
                    <span class="text-body-secondary">{{ __('This section has no connected records yet.') }}</span>
                </td></tr>
            </tbody>
        </table>
    </div>
    @endif
@endsection
