@extends('layouts.app')

@section('title', 'Find flights')

@section('content')
<div class="flight-search-page">
    <h1 class="flight-page-title">Find flights</h1>
    @if($flights === null)
        <div class="panel flight-search-panel">
            <x-landing-search class="landing-search" />
        </div>
    @else
        <details class="panel flight-search-panel" open>
            <summary><strong>Search, filters, and sort</strong></summary>
            <div class="stack" style="margin-top:1rem">
                <section aria-labelledby="flight-search-title">
                    <h2 id="flight-search-title">Search</h2>
                    <x-landing-search class="landing-search" />
                </section>
                <section aria-labelledby="flight-filter-title">
                    <h2 id="flight-filter-title">Filter and sort results</h2>
                    <form method="GET" action="{{ route('flights.search') }}" class="search-refinements">
                        @foreach($filters as $key => $value)
                            @if($value !== null && !in_array($key, ['sort', 'min_price', 'budget']))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <x-travel-field name="min_price" label="Minimum fare (JOD)" type="number" min="0" step="0.01" :value="$filters['min_price'] ?? null" />
                        <x-travel-field name="budget" label="Maximum fare (JOD)" type="number" min="0" step="0.01" placeholder="No limit" :value="$filters['budget'] ?? null" />
                        <x-travel-field name="sort" label="Sort flights" :value="$filters['sort']" :options="['departure_asc' => 'Earliest departure', 'departure_desc' => 'Latest departure', 'price_asc' => 'Lowest price', 'price_desc' => 'Highest price']" />
                        <div class="actions">
                            <button class="button" type="submit">Apply filters</button>
                            <a class="button secondary" href="{{ route('flights.search', array_merge($filters, ['min_price' => null, 'budget' => null, 'sort' => 'departure_asc'])) }}">Reset</a>
                        </div>
                    </form>
                </section>
            </div>
        </details>
    @endif
    @if($flights !== null)
        <header class="flight-results-heading">
            <h2>{{ $filters['leg'] === 'return' ? 'Return flights' : 'Outbound flights' }}</h2>
            <span>{{ trans_choice(':count flight|:count flights', $flights->total()) }}</span>
        </header>
        @if(!isset($filters['adults']))
            <p class="notice">Choose the adult, youth and child counts in the search form and search again before selecting flights.</p>
        @endif
        @if($filters['leg'] === 'return')
            <a class="button secondary flight-change-outbound" href="{{ route('flights.search', array_diff_key(array_merge($filters, ['leg' => 'outbound']), ['outbound_class_id' => true])) }}">← Change outbound selection</a>
        @endif
        <div class="stack section">
        @forelse($flights as $flight)
            <article class="panel flight-result">
                <x-flight-card :flight="$flight" :preferred-cabin="$filters['cabin'] ?? null" :show-fare="false" />
                @foreach($flight->flightClasses as $class)
                    <div class="flight-result__fare">
                        <p class="flight-fare-cabin"><strong>{{ ucfirst($class->class) }}</strong><span>{{ $class->available_seats_count }} seats available</span></p>
                        <p class="flight-fare-price"><strong>JOD {{ $class->price }}</strong><span>per seat</span></p>
                        @if(isset($filters['adults']))
                        @if($filters['trip'] === 'round_trip' && $filters['leg'] === 'outbound')
                            <a class="button" href="{{ route('flights.search', array_merge($filters, ['leg' => 'return', 'outbound_class_id' => $class->id])) }}">Select outbound and find return</a>
                        @else
                            <form method="POST" action="{{ route('flights.selection') }}">
                                @csrf
                                @foreach($filters as $key => $value)
                                    @if($value !== null && $key !== 'outbound_class_id')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                                @endforeach
                                <input type="hidden" name="outbound_class_id" value="{{ $filters['leg'] === 'return' ? $filters['outbound_class_id'] : $class->id }}">
                                @if($filters['leg'] === 'return')<input type="hidden" name="return_class_id" value="{{ $class->id }}">@endif
                                <button class="button" type="submit">Confirm flight selection</button>
                            </form>
                        @endif
                        @endif
                    </div>
                @endforeach
                @if($flights->count() > 1)
                    <footer class="flight-result__footer">
                        <label class="flight-compare-choice">
                            <input type="checkbox" name="flights[]" value="{{ $flight->id }}" form="compare-flights">
                            <span>Compare this flight</span>
                        </label>
                    </footer>
                @endif
            </article>
        @empty
            <p class="notice">No available flights match this search.</p>
        @endforelse
        </div>
        @if($flights->count() > 1)
            <form method="GET" action="{{ route('compare') }}" id="compare-flights" class="panel flight-compare-bar" data-compare>
                <p id="flight-compare-status" data-compare-status role="status">Select 2 or 3 flights.</p>
                <button class="button" type="submit" aria-describedby="flight-compare-status">Compare 2 or 3 flights</button>
            </form>
        @endif
        {{ $flights->links() }}
    @endif
</div>
@endsection
