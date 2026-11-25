    @if($activeConversation->type !== 'tips' && empty($transcript['flights']))
        <p class="notice">{{ __('No database flights matched your filters.') }}</p>
    @endif
    @if(!empty($transcript['flights']))
        <h2>{{ __('Database flight results') }}</h2>
        <p>Prices and availability recorded at {{ $transcript['recorded_at'] }}. Times use {{ $transcript['timezone'] }}. Search again for current availability.</p>
        <div class="table-wrap panel section"><table class="table table-bordered">
            <thead><tr><th>{{ __('Flight') }}</th><th>{{ __('Airline') }}</th><th>{{ __('Route and times') }}</th><th>{{ __('Cabins, base fares and baggage') }}</th></tr></thead><tbody>
            @foreach($transcript['flights'] as $flight)
                <tr><td><a href="{{ route('flights.show', $flight['id']) }}">{{ $flight['number'] }} (#{{ $flight['id'] }})</a></td><td>{{ $flight['airline'] }}</td><td>{{ $flight['from'] }} → {{ $flight['to'] }}<br>{{ $flight['departure'] }} → {{ $flight['arrival'] }}</td><td>
                    @foreach($flight['cabins'] as $class)
                        <p>{{ ucfirst($class['class']) }} — {{ $class['price'] }}<br>{{ __('Cabin') }} {{ $class['cabin_baggage_kg'] }} kg; checked {{ $class['checked_baggage_kg'] }} kg<br>{{ $class['available_seats'] }} seats available at search time</p>
                    @endforeach
                </td></tr>
            @endforeach
            </tbody>
        </table></div>
    @endif
