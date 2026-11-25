@extends('layouts.app')

@php
    $demoFlights = require resource_path('views/shared/demo-flights.php');
    $selection = array_filter(request()->only(['flight', 'class']), 'is_string');
    $flight = $demoFlights->firstWhere('id', (int) ($selection['flight'] ?? 0));
    $class = $selection['class'] ?? '';
    $fare = $flight['fares'][$class] ?? null;
    $cabinRows = require resource_path('views/shared/demo-cabin.php');
    $selectedSeat = request()->query('seat');
    if (is_string($selectedSeat) && preg_match('/^(\d{2})([ACHK])$/D', $selectedSeat, $seatParts)
        && ($cabinRows[(int) $seatParts[1]][$seatParts[2]] ?? false)) {
        $selection['seat'] = $selectedSeat;
    }
@endphp

@section('title'){{ __($title) }}@endsection

@section('content')
<nav class="subnav" aria-label="{{ __('Booking steps') }}">
    @foreach($steps as $key => $label)
        <a href="{{ route("book.$key", $selection) }}" @if($key === $step) aria-current="step" @endif>{{ $loop->iteration }}. {{ __($label) }}</a>
    @endforeach
</nav>
<h1>{{ __($title) }}</h1>
<p class="notice">{{ __('Booking preview. Do not enter real passport or payment details.') }}</p>
@unless($step === 'passengers')
@if($flight)
    <p>{{ $flight['number'] }} · {{ $flight['from'] }} → {{ $flight['to'] }} @if($fare) · {{ __(ucfirst($class)) }} · JOD {{ $fare[0] }} @endif</p>
@else
    <p>{{ __('Choose a flight and fare to see sample trip details.') }} <a href="{{ route('flights.search') }}">{{ __('Find flights') }}</a></p>
@endif
@endunless
@switch($step)
    @case('passengers')
        @include('booking.passengers')
        @break
    @case('seats')
        <section class="panel">
            <h2>{{ __('Choose a sample seat') }}</h2>
            <p>{{ __('Seat selection is local to this preview. Live holds are unavailable.') }}</p>
            <form method="GET" action="{{ route('book.review') }}">
                @foreach(\Illuminate\Support\Arr::except($selection, 'seat') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <x-booking-seat-map :rows="$cabinRows" :selected="$selection['seat'] ?? null" />
                <button class="button section">{{ __('Review sample trip') }}</button>
            </form>
        </section>
        @break
    @case('review')
        <section class="panel">
            <h2>{{ __('Trip summary') }}</h2>
            @isset($selection['seat'])
                <p>{{ __('Selected seat') }} <strong>{{ $selection['seat'] }}</strong></p>
            @endisset
            <p>{{ $flight ? 'Departs at '.$flight['departure'] : 'Choose a flight to see trip details.' }}</p>
            <p>{{ $fare ? 'Base fare per traveler: JOD '.$fare[0] : 'Choose a fare to see the base price.' }}</p>
            <p>{{ __('Final taxes, credits, passenger details, and cancellation terms appear when checkout is available.') }}</p>
            <a class="button" href="{{ route('book.payment', $selection) }}">{{ __('Preview payment') }}</a>
        </section>
        @break
    @case('payment')
        <section class="panel"><h2>{{ __('Order payment') }}</h2><p>{{ __('Stripe checkout and credit redemption are currently unavailable.') }}</p><button class="button" disabled>{{ __('Pay securely') }}</button> <a class="button secondary" href="{{ route('book.confirmation', $selection) }}">{{ __('Preview confirmation') }}</a></section>
        @break
    @case('confirmation')
        <section class="panel"><h2>{{ __('No confirmed booking') }}</h2><p>{{ __('Confirmed reservations will appear in your bookings account once checkout is connected.') }}</p><a class="button" href="{{ route('my.bookings') }}">{{ __('Preview my bookings') }}</a></section>
        @break
@endswitch
@endsection
