@extends('layouts.app')

@section('title', __('Destinations'))
@section('body-class', 'landing')

@php
    $currencyNames = ['JOD' => 'Jordanian Dinar', 'AED' => 'UAE Dirham', 'EGP' => 'Egyptian Pound', 'TRY' => 'Turkish Lira', 'SAR' => 'Saudi Riyal', 'QAR' => 'Qatari Riyal', 'GBP' => 'British Pound', 'EUR' => 'Euro', 'THB' => 'Thai Baht', 'JPY' => 'Japanese Yen', 'USD' => 'US Dollar'];
    $currencyLabel = fn ($code) => $code ? ($code.(isset($currencyNames[$code]) ? ' - '.$currencyNames[$code] : '')) : null;
@endphp

@section('content')
<div class="landing-wrap destination-directory">
    <h1>{{ __('Explore destinations') }}</h1>
    <p class="muted">Each destination includes its country details, currency, and the cities and airports you can travel through.</p>
    @if($countries->isNotEmpty())
        <div class="directory-grid">
            @foreach($countries as $country)
                <article class="panel directory-card destination-group">
                    <div class="directory-copy">
                        <p class="directory-country">Destination</p>
                        <h2>{{ $country->name }} @if($country->code)<span class="muted">({{ $country->code }})</span>@endif</h2>
                        <div class="form-grid destination-meta-grid" style="margin-top:.75rem">
                            <p><strong>Currency:</strong> {{ $currencyLabel($country->currency) ?: '—' }}</p>
                            <p><strong>Language:</strong> {{ $country->language ?: '—' }}</p>
                            @if($country->local_apps)
                                <p class="col-span-2"><strong>Local apps:</strong> {{ $country->local_apps }}</p>
                            @endif
                        </div>

                        <section class="section" aria-label="Cities in {{ $country->name }}">
                            <h3>Cities and airports in this destination</h3>
                            @forelse($country->cities as $city)
                                <div class="panel" style="margin-top:.75rem">
                                    <h4 style="margin:0 0 .35rem">{{ $city->name }}</h4>
                                    @if($city->best_season)<p><strong>Best season:</strong> {{ $city->best_season }}</p>@endif
                                    @if($city->airport_transport)<p><strong>Airport transport:</strong> {{ $city->airport_transport }}</p>@endif
                                    @if($city->tips)<p><strong>Travel tips:</strong> {{ $city->tips }}</p>@endif
                                    @if($city->airports->isNotEmpty())
                                        <p><strong>Airports:</strong>
                                            {{ $city->airports->map(fn ($airport) => $airport->name.' ('.$airport->code.')')->join(', ') }}
                                        </p>
                                    @else
                                        <p class="muted">Airport information coming soon.</p>
                                    @endif
                                </div>
                            @empty
                                <p class="muted">City and airport information will be available soon.</p>
                            @endforelse
                        </section>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <p>Destination information will be available soon.</p>
    @endif
</div>
@endsection
