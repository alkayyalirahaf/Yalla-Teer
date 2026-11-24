@props(['filters' => []])

@php
    $demoFlights = require resource_path('views/shared/demo-flights.php');
    $airports = $demoFlights->pluck('origin', 'from')->union($demoFlights->pluck('destination', 'to'))
        ->sortKeys()->map(fn ($name, $code) => "$name ($code)")->all();
    $airports += ['CDG' => 'Paris Charles de Gaulle (CDG)', 'IST' => 'Istanbul Airport (IST)'];
    ksort($airports);
    $travelers = [
        'adults' => ['Adults', 1, $filters['passengers'] ?? 1],
        'youth' => ['Youth (12–16 years)', 0, 0],
        'children' => ['Children (2–12 years)', 0, 0],
        'infants' => ['Infants (under 2 years)', 0, 0],
    ];
@endphp

<form method="GET" action="{{ route('flights.search') }}" class="panel form-grid" id="flight-search">
    <x-travel-field name="from" label="From" :value="strtoupper($filters['from'] ?? '')" :options="['' => 'Any departure airport'] + $airports" />
    <x-travel-field name="to" label="To" :value="strtoupper($filters['to'] ?? '')" :options="['' => 'Any arrival airport'] + $airports" />
    <x-travel-field name="depart" label="Departure" type="date" :value="$filters['depart'] ?? null" />
    <x-travel-field name="return" label="Return (optional)" type="date" :value="$filters['return'] ?? null" />
    <x-travel-field name="cabin" label="Cabin" :value="$filters['cabin'] ?? ''" :options="['' => 'Any cabin', 'economy' => 'Economy', 'business' => 'Business', 'first' => 'First']" />
    <x-travel-field name="budget" label="Max fare (JOD)" type="number" min="0" step="0.01" :value="$filters['budget'] ?? null" />
    <fieldset class="wide traveler-fields">
        <legend>{{ __('Travelers') }}</legend>
        <div class="form-grid">
            @foreach($travelers as $name => [$label, $minimum, $default])
                @php
                    $counts = range($minimum, 9);
                @endphp
                <x-travel-field :name="$name" :label="$label" :value="$filters[$name] ?? $default" :options="array_combine($counts, $counts)" />
            @endforeach
        </div>
    </fieldset>
    @if(request()->routeIs('flights.search'))
        <x-travel-field name="stops" label="Stops" :value="$filters['stops'] ?? ''" :options="['' => 'Any', 0 => 'Nonstop', 1 => '1 stop', 2 => '2 stops']" />
        <x-travel-field name="sort" label="Sort by departure" :value="$filters['sort'] ?? 'earliest'" :options="['earliest' => 'Earliest first', 'latest' => 'Latest first']" />
    @endif
    <div class="wide"><button class="button">{{ __('Find flights') }}</button></div>
</form>
