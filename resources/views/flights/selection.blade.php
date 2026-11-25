@extends('layouts.app')

@section('title', __('Selected flights'))

@section('content')
@php
    $selectedFlights = $loadSelectedFlights();
    $backFilters = $selection['filters'] ?? [];
    unset($backFilters['leg'], $backFilters['outbound_class_id'], $backFilters['return_class_id']);
@endphp
<div class="flight-selection stack">
    <header>
        <a class="selection-back" href="{{ route('flights.search', $backFilters) }}">{{ __('← Back to results') }}</a>
        <h1 class="flight-page-title">{{ __('Selected flights') }}</h1>
    </header>
    <div class="selection-layout">
        <section class="panel stack" aria-labelledby="selection-itinerary">
            <h2 id="selection-itinerary">{{ __('Itinerary') }}</h2>
        @foreach(['outbound' => 'Outbound', 'return' => 'Return'] as $key => $label)
            @continue(!$selection[$key])
            @php($flight = $selectedFlights->get($selection[$key]['flight_id']))
            <div class="selection-leg">
                <div class="selection-airline">
                    @if(in_array($flight?->airline?->code, ['RJ', 'EK', 'TK'], true))
                        <img data-airline-image src="https://www.gstatic.com/flights/airline_logos/70px/{{ $flight->airline->code }}.png" alt="" width="54" height="54">
                    @endif
                    <div>
                        <h3>{{ __($label) }}</h3>
                        <a href="{{ route('flights.show', $selection[$key]['flight_id']) }}">{{ $flight?->flight_number ?? ('Flight #'.$selection[$key]['flight_id']) }}</a>
                        @if($flight?->airline)<small>{{ $flight->airline->name }}</small>@endif
                    </div>
                </div>
                <div class="selection-fare"><strong>JOD {{ $selection[$key]['price'] }}</strong><small>{{ __('Base fare per seat') }}</small></div>
            </div>
        @endforeach
        </section>
        <aside class="panel stack" aria-labelledby="selection-travelers">
            <h2 id="selection-travelers">{{ __('Travelers') }}</h2>
            <p>{{ $selection['trip'] === 'round_trip' ? 'Round trip' : 'One way' }} · {{ trans_choice(':count seat|:count seats', $selection['passengers']) }}</p>
            <ul>
                @foreach(['adults' => 'Adult|Adults', 'youth' => 'Youth', 'children' => 'Child|Children', 'infants' => 'Infant|Infants'] as $key => $labels)
                    @if($count = $selection['passenger_counts'][$key])
                        <li>{{ $count }} {{ trans_choice($labels, $count) }}</li>
                    @endif
                @endforeach
            </ul>
            <a class="button" href="{{ route('book.passengers') }}">{{ __('Continue to passengers') }} <span aria-hidden="true">→</span></a>
        </aside>
    </div>
    <p class="selection-note"><span aria-hidden="true">ⓘ</span> {{ __('Seats are not reserved yet. Fares may change before booking.') }}</p>
</div>
@endsection
