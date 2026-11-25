@extends('layouts.app')
@section('title', __('Select seats'))
@section('content')
<div class="stack seat-selection">
    <header>
        <a class="selection-back" href="{{ route('book.passengers') }}">{{ __('← Back to travelers') }}</a>
        <h1 class="flight-page-title">{{ __('Select seats') }}</h1>
        <p>{{ __('Choose one seat for each traveler. Your seats are held for 15 minutes after you continue.') }}</p>
    </header>
    <form class="stack" method="POST" action="{{ route('book.seats.store') }}">
        @csrf
        <input type="hidden" name="booking_code" value="{{ $code }}">
        @foreach($cabins as $direction => $cabin)
            <section class="panel stack seat-flight" data-seat-picker>
                <div class="seat-flight-heading">
                    <div>
                        <p>{{ __(ucfirst($direction)) }}</p>
                        <h2>{{ $cabin->flight->flight_number }}</h2>
                    </div>
                    <span class="seat-flight-cabin">{{ __(ucfirst($cabin->class)) }}</span>
                </div>
                <div class="actions seat-traveler-switch" data-seat-traveler-switch hidden>
                    @foreach($passengers as $index => $passenger)
                        <button type="button" class="button secondary" data-seat-traveler="{{ $index }}" aria-pressed="false">
                            {{ $passenger['first_name'] }} {{ $passenger['last_name'] }}
                            <span data-seat-summary></span>
                        </button>
                    @endforeach
                </div>
                <div class="seat-map-travelers">
                    @foreach($passengers as $index => $passenger)
                        <fieldset class="seat-map-traveler" data-seat-panel>
                            <legend>{{ $passenger['first_name'] }} {{ $passenger['last_name'] }}</legend>
                            <x-booking-seat-map :rows="$maps[$direction]['rows']"
                                :seat-ids="$maps[$direction]['ids']"
                                :name="'seats['.$direction.']['.$index.']'"
                                :selected="old('seats.'.$direction.'.'.$index)" />
                        </fieldset>
                    @endforeach
                </div>
            </section>
        @endforeach
        <div class="actions seat-selection-actions">
            <button class="button" type="submit">{{ __('Review booking') }}</button>
        </div>
    </form>
</div>
@endsection
