@extends('super-admin.management-layout')
@section('admin-title', $city->exists ? 'Edit city' : 'Add city')

@section('admin-content')
    <a class="btn btn-outline-secondary mb-3" href="{{ route('admin.cities') }}">{{ __('← Back to cities') }}</a>
    <h1>{{ $city->exists ? 'Edit city' : 'Add city' }}</h1>
    @if(!$countries)
        <div class="alert alert-info">{{ __('Add a country before creating a city.') }} <a href="{{ route('admin.countries') }}">{{ __('View countries') }}</a></div>
    @endif
    <form class="panel p-3 p-md-4 stack" method="POST" action="{{ $city->exists ? route('admin.cities.update', $city) : route('admin.cities.store') }}">
        @csrf
        @if($city->exists) @method('PATCH') @endif
        <div class="form-grid">
            <x-travel-field name="name" label="City name" :value="$city->name" maxlength="100" required />
            <x-travel-field name="country_id" label="Country" :value="$city->country_id" :options="['' => 'Select a country'] + $countries" required />
            <x-travel-field name="best_season" label="Best season (optional)" :value="$city->best_season" maxlength="100" />
            <x-travel-field name="airport_transport" label="Airport transport (optional)" type="textarea" :value="$city->airport_transport" maxlength="10000" />
            <x-travel-field name="tips" label="Travel tips (optional)" type="textarea" :value="$city->tips" maxlength="10000" />
        </div>
        <div><button type="submit" class="btn btn-primary" @disabled(!$countries)>{{ __('Save city') }}</button></div>
    </form>
@endsection
