@extends('layouts.app')

@section('title', __('Destinations'))
@section('body-class', 'landing')

@php
    $cityPhotos = [
        'amman' => 'amman.webp',
        'dubai' => 'dubai.webp',
        'istanbul' => 'istanbul.webp',
        'paris' => 'paris.webp',
        'london' => 'catalog/lond.webp',
        'tokyo' => 'catalog/jp.webp',
        'bangkok' => 'catalog/th.webp',
    ];
    $cityImageUrls = [
        'cairo' => 'https://www.voyageegypte.fr/cdn/eg-public/citadelle_caire_vue_terasse.jpg',
        'rome' => 'https://images.unsplash.com/photo-1711447402543-d9acc369fbe4?w=800&fit=crop',
    ];
@endphp

@section('content')
<div class="landing-wrap destination-directory">
    <h1>{{ __('Explore destinations') }}</h1>
    @if($countries->contains(fn ($country) => $country->cities->isNotEmpty()))
        <div class="directory-grid">
            @foreach($countries as $country)
                @foreach($country->cities as $city)
                @php
                    $slug = \Illuminate\Support\Str::slug($city->name);
                    $photo = isset($cityPhotos[$slug])
                        ? asset('assets/landing/'.$cityPhotos[$slug])
                        : ($cityImageUrls[$slug] ?? null);
                @endphp
                <article class="panel directory-card">
                    <div class="directory-photo">
                        @if($photo)
                            <img src="{{ $photo }}" data-directory-photo alt="" loading="lazy" decoding="async" width="640" height="360">
                        @endif
                    </div>
                    <div class="directory-copy">
                        <p class="directory-country">{{ __($country->name) }}</p>
                        <h2>{{ __($city->name) }}</h2>
                        @if($city->best_season)<p><strong>{{ __('Best season:') }}</strong> {{ __($city->best_season) }}</p>@endif
                        @if($city->airport_transport)<p><strong>{{ __('Airport transport:') }}</strong> {{ __($city->airport_transport) }}</p>@endif
                        @if($city->tips)<p><strong>{{ __('Travel tips:') }}</strong> {{ __($city->tips) }}</p>@endif
                        @if($city->airports->isNotEmpty())
                            <h3>{{ __('Airports') }}</h3>
                            <ul>
                                @foreach($city->airports as $airport)
                                    <li>{{ __($airport->name) }} ({{ $airport->code }})</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </article>
                @endforeach
            @endforeach
        </div>
    @else
        <p>{{ __('Destination information will be available soon.') }}</p>
    @endif
</div>
@endsection
