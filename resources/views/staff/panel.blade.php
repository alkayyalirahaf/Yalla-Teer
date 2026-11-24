@extends('layouts.app')

@php
    $requiredRole = $area === 'airline' ? 'airlineAdmin' : 'superAdmin';
    if (!app()->isLocal() || auth()->check()) {
        abort_unless(auth()->user()?->{$requiredRole}, 403);
    }
@endphp

@section('title'){{ $title }}@endsection

@section('content')
<p class="eyebrow">{{ $area === 'airline' ? 'Airline desk' : 'Platform administration' }}</p>
<h1>{{ $title }}</h1>
@unless(isset($statistics) || isset($transactions))<p class="notice">Staff interface preview. No live records or management actions are connected.</p>@endunless
@if($area === 'admin')
    <nav class="actions section" aria-label="Administration"><a class="button secondary" href="{{ route('admin.dashboard') }}">Dashboard</a><a class="button secondary" href="{{ route('admin.countries') }}">Destinations</a><a class="button secondary" href="{{ route('admin.credits') }}">Credits</a></nav>
@endif
@switch($screen)
    @case('credits')
        @include('super-admin.credits.content')
        @break
    @case('dashboard')
        @if(isset($statistics))
            @include('super-admin.dashboard.content')
        @else
        <div class="cards">
            @foreach(['Flights' => '24', 'Upcoming flights' => '8', 'Bookings' => '136', $area === 'airline' ? 'Seats sold' : 'Airlines awaiting approval' => $area === 'airline' ? '68%' : '2'] as $label => $value)
                <article class="panel"><p>{{ $label }}</p><h2>{{ $value }}</h2></article>
            @endforeach
        </div>
        <div class="actions section"><a class="button" href="{{ route("$area.flights") }}">View flights</a><a class="button secondary" href="{{ route("$area.bookings") }}">View bookings</a></div>
        @endif
        @break
    @case('booking')
        <x-booking-summary />
        @break
    @case('flight')
        <div class="panel"><h2>YT 402 · AMM → DXB</h2><x-seat-map /><a class="button section" href="{{ route('airline.edit', request()->route('id')) }}">Preview edit</a></div>
        @break
    @case('create')
    @case('edit')
        <form method="post" class="panel stack" data-preview-form>
            <div class="form-grid">
                <x-travel-field name="flight_number" label="Flight number" :value="$screen === 'edit' ? 'YT 402' : null" required />
                <x-travel-field name="origin" label="Origin airport" :value="$screen === 'edit' ? 'AMM' : null" required maxlength="3" />
                <x-travel-field name="destination" label="Destination airport" :value="$screen === 'edit' ? 'DXB' : null" required maxlength="3" />
                <x-travel-field name="departure" label="Departure" type="datetime-local" required />
                <x-travel-field name="arrival" label="Arrival" type="datetime-local" required />
                <x-travel-field name="stops" label="Stops" type="number" min="0" value="0" required />
            </div>
            <fieldset class="form-grid">
                <legend>Economy cabin</legend>
                <x-travel-field name="price" label="Fare (JOD)" type="number" min="0" required />
                <x-travel-field name="cabin_baggage" label="Cabin baggage (kg)" type="number" min="0" value="7" />
                <x-travel-field name="checked_baggage" label="Checked baggage (kg)" type="number" min="0" value="23" />
            </fieldset>
            <x-seat-map />
            <button class="button" disabled>Preview flight changes</button>
            <p data-preview-status role="status"></p>
        </form>
        @break
    @default
        @if($screen === 'flights' && $area === 'airline')<a href="{{ route('airline.create') }}" class="button">Preview create flight</a>@endif
        @php
            $sample = match ("$area.$screen") {
                'admin.flights' => ['YT 402', 'Yalla Teer Demo', 'AMM → DXB', '08:10', 'Scheduled'],
                'airline.flights' => ['YT 402', 'AMM → DXB', '08:10', 'Scheduled'],
                'admin.bookings' => ['YTDEMO', 'AMM → DXB', 'Confirmed', 'JOD 145'],
                'airline.bookings' => ['YTDEMO', 'AMM → DXB', 'Confirmed'],
                'admin.countries' => ['Jordan', 'JO', 'JOD', 'Arabic', ''],
                'admin.cities' => ['Amman', 'Jordan', 'Airport bus and taxis', 'Check operating hours', 'Spring and autumn'],
                'admin.airports' => ['Queen Alia International', 'AMM', 'Amman'],
                default => [],
            };
            $detail = $screen === 'bookings' ? "$area.booking" : ($area === 'airline' && $screen === 'flights' ? 'airline.flight' : null);
        @endphp
        <section class="panel table-wrap section">
            <table>
                <caption>{{ $title }}</caption>
                <thead><tr>@foreach($columns as $column)<th scope="col">{{ $column }}</th>@endforeach @if($detail)<th scope="col">Details</th>@endif</tr></thead>
                <tbody>
                    @if($sample)
                        <tr>@foreach($sample as $value)<td>{{ $value }}</td>@endforeach @if($detail)<td><a href="{{ route($detail, 1) }}">View sample</a></td>@endif</tr>
                    @else
                        <tr><td colspan="{{ count($columns) }}">No sample records to display.</td></tr>
                    @endif
                </tbody>
            </table>
        </section>
@endswitch
@endsection
