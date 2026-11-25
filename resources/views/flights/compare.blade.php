@extends('layouts.app')

@section('title', __('Compare flights'))

@section('content')
<h1 class="flight-page-title">{{ __('Compare flights') }}</h1>
<div class="actions"><a class="button secondary" href="{{ route('flights.search', session('dev3.search_filters', [])) }}">{{ __('Back to search results') }}</a></div>
@if($flights->count() < 2)
    <p class="panel section">{{ __('Choose two or three flights from the search results to compare.') }}</p>
@else
    <div class="cards section">@foreach($flights as $flight)<x-flight-card :flight="$flight" />@endforeach</div>
    <div class="table-wrap panel section">
        <table><caption>{{ __('Cabin fares, baggage and available seats') }}</caption>
            <thead><tr><th scope="col">{{ __('Cabin') }}</th>@foreach($flights as $flight)<th scope="col">{{ $flight->flight_number }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach(['economy', 'business', 'first'] as $cabin)
                @php($best = $flights->flatMap(fn ($flight) => $flight->flightClasses->where('class', $cabin))->min('price'))
                <tr><th scope="row">{{ __(ucfirst($cabin)) }}</th>
                @foreach($flights as $flight)
                    @php($class = $flight->flightClasses->firstWhere('class', $cabin))
                    <td @class(['best' => $class && $class->price == $best])>
                        @if($class)
                            JOD {{ $class->price }}<br>{{ $class->cabin_baggage_kg }} kg cabin · {{ $class->checked_baggage_kg }} kg checked<br>{{ $class->available_seats_count }} seats available
                        @else Unavailable @endif
                    </td>
                @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <p>Times use {{ config('app.timezone') }}. Prices are per-seat base fares.</p>
@endif
@endsection
