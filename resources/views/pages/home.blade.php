@extends('layouts.app')

@section('title'){{ __($title) }}@endsection

@section('content')
<section class="hero">
    <div>
        <p class="eyebrow">{{ __('Flight reservations') }}</p>
        <h1>{{ __('Your next journey starts here.') }}</h1>
        <p>{{ __('Search routes, compare cabins, and explore your destination before you fly.') }}</p>
        <a href="{{ route('destinations') }}">{{ __('Explore destinations →') }}</a>
    </div>
    <x-travel-search />
</section>
<section class="section">
    <h2>{{ __('Plan your trip') }}</h2>
    <div class="cards">
        <a class="panel tile" href="{{ route('flights.search') }}"><h3>{{ __('Find a flight') }}</h3><p>{{ __('Browse upcoming routes and compare schedules.') }}</p></a>
        <a class="panel tile" href="{{ route('destinations') }}"><h3>{{ __('Destination guide') }}</h3><p>{{ __('Look up local languages, currencies, and travel tips.') }}</p></a>
        <a class="panel tile" href="{{ route('airlines.index') }}"><h3>{{ __('Airlines') }}</h3><p>{{ __('See available airlines, ratings, and customer comments.') }}</p></a>
        <a class="panel tile" href="{{ route('booking.lookup') }}"><h3>{{ __('Find your booking') }}</h3><p>{{ __('Use your booking code and last name.') }}</p></a>
    </div>
</section>
@endsection
