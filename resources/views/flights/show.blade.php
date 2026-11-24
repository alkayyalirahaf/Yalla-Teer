@extends('layouts.app')

@section('title', __('Flight details'))

@section('content')
@php
    $fleetPhoto = match ($flight->airline->code) {
        'RJ' => 'https://cdn.jetphotos.com/full/5/1193137_1776760492.jpg',
        'EK' => 'https://www.aerotime.aero/images/2024/11/DXB1-32.jpg',
        'TK' => 'https://w0.peakpx.com/wallpaper/547/707/HD-wallpaper-turkish-airlines-boeing-737-airbus-a321-turkish-airlines-logo-on-empennage-passenger-airplanes-red-tails-with-logo-airport-turkey-turkish-airlines-logo.jpg',
        default => null,
    };
@endphp
<h1 class="flight-page-title">{{ $flight->flight_number }}</h1>
<div class="panel">
    <span class="badge">{{ $flight->airline->name }} · {{ __(ucfirst($flight->status)) }}</span>
    <h2 class="section">{{ $flight->originAirport->name }} ({{ $flight->originAirport->code }}) → {{ $flight->destinationAirport->name }} ({{ $flight->destinationAirport->code }})</h2>
    <p>{{ $flight->departure_at->format('Y-m-d H:i') }} → {{ $flight->arrival_at->format('Y-m-d H:i') }} · {{ (int) $flight->departure_at->diffInMinutes($flight->arrival_at) }} minutes</p>
    <p>Times use {{ config('app.timezone') }}. Prices are per-seat base fares.</p>
</div>
@if($fleetPhoto)
    <figure class="flight-fleet-photo" data-airline-photo>
        <img data-airline-image src="{{ $fleetPhoto }}" alt="{{ $flight->airline->name }} aircraft">
        <figcaption>Fleet photo · {{ $flight->airline->name }}</figcaption>
    </figure>
@endif
<section class="section">
    <h2>{{ __('Available cabins') }}</h2>
    <div class="cards">
        @forelse($flight->flightClasses as $class)
            <article class="panel stack">
                <h3>{{ __(ucfirst($class->class)) }}</h3><p>JOD {{ $class->price }}</p>
                <p>{{ $class->cabin_baggage_kg }} kg cabin · {{ $class->checked_baggage_kg }} kg checked</p>
                <p>{{ $class->available_seats_count }} seats available</p>
                <a class="button secondary" href="{{ route('flights.search', array_merge(session('dev3.search_filters', []), ['cabin' => $class->class])) }}">{{ __('Search and select this cabin') }}</a>
            </article>
        @empty
            <p class="panel">{{ __('No cabins have been published.') }}</p>
        @endforelse
    </div>
</section>
<a class="button secondary section" href="{{ route('flights.search', session('dev3.search_filters', [])) }}">{{ __('Back to results and select a cabin') }}</a>
@endsection
