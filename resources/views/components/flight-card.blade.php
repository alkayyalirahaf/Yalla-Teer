@props(['flight', 'preferredCabin' => null, 'showFare' => true])
@php
    if ($flight instanceof \App\Models\Flight) {
        $flight = [
            'airline_code' => $flight->airline->code,
            'id' => $flight->id, 'airline' => $flight->airline->name, 'number' => $flight->flight_number,
            'from' => $flight->originAirport->code, 'to' => $flight->destinationAirport->code,
            'departure' => $flight->departure_at->format('Y-m-d H:i'),
            'arrival' => $flight->arrival_at->format('Y-m-d H:i'),
            'duration' => __(':count minutes', ['count' => (int) $flight->departure_at->diffInMinutes($flight->arrival_at)]),
            'stops' => $flight->stops,
            'fares' => $flight->flightClasses->mapWithKeys(fn ($class) => [$class->class => [$class->price, $class->cabin_baggage_kg, $class->checked_baggage_kg]])->all(),
        ];
    }
    $departure = \Illuminate\Support\Carbon::parse($flight['departure']);
    $arrival = \Illuminate\Support\Carbon::parse($flight['arrival']);
@endphp
<article class="panel flight-card flight-itinerary">
    <div class="flight-itinerary__body">
        <span class="flight-airline">
            @if(in_array($flight['airline_code'] ?? null, ['RJ', 'EK', 'TK'], true))
                <img data-airline-image src="https://www.gstatic.com/flights/airline_logos/70px/{{ $flight['airline_code'] }}.png" alt="" width="58" height="58">
            @endif
            <span>{{ $flight['airline'] }}</span>
            <span class="flight-airline__number">· {{ $flight['number'] }}</span>
        </span>
        <div class="flight-timeline">
            <div class="flight-timeline__endpoint">
                <span>{{ __('Departure ·') }} <strong>{{ $flight['from'] }}</strong></span>
                <time datetime="{{ $departure->format('Y-m-d\TH:i') }}"><strong>{{ $departure->format('H:i') }}</strong><span>{{ $departure->locale(app()->getLocale())->translatedFormat('D, d M Y') }}</span></time>
            </div>
            <div class="flight-timeline__duration">
                <span>{{ $flight['duration'] }}</span>
                <span>{{ trans_choice('{0} Nonstop|{1} :count stop|[2,*] :count stops', $flight['stops']) }}</span>
            </div>
            <div class="flight-timeline__endpoint">
                <span>{{ __('Arrival ·') }} <strong>{{ $flight['to'] }}</strong></span>
                <time datetime="{{ $arrival->format('Y-m-d\TH:i') }}"><strong>{{ $arrival->format('H:i') }}</strong><span>{{ $arrival->locale(app()->getLocale())->translatedFormat('D, d M Y') }}</span></time>
            </div>
        </div>
        @if($showFare)
            <p>{{ $flight['fares'] ? __('From JOD :amount', ['amount' => min(array_column($flight['fares'], 0))]) : __('No available cabins') }}</p>
        @endif
    </div>
    <div class="actions">
        {{ $slot }}
        <a class="button secondary" href="{{ route('flights.show', array_filter(['flight' => $flight['id'], 'cabin' => $preferredCabin])) }}">{{ __('Flight details') }}</a>
    </div>
</article>
