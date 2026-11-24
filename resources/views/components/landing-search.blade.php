@php
    $airports = \App\Models\Airport::with('city')->orderBy('code')->get()
        ->mapWithKeys(fn ($airport) => [$airport->code => $airport->code.' · '.$airport->name.' ('.$airport->city->name.')'])->all();
    $selectedTrip = str_replace('_', '-', old('trip', request('trip', 'round-trip')));
@endphp
<form {{ $attributes->merge(['class' => 'landing-search']) }} method="GET" action="{{ route('flights.search') }}"
    data-traveler-one="{{ __('1 traveler') }}" data-traveler-many="{{ __(':count travelers') }}"
    data-swap-error="{{ __('Choose a different arrival airport.') }}">
    <div class="landing-search-options">
    <fieldset class="trip-options">
        <legend class="sr-only">{{ __('Journey type') }}</legend>
        <label><input class="sr-only" type="radio" name="trip" value="round-trip" @checked($selectedTrip === 'round-trip')><span>{{ __('Round Trip') }}</span></label>
        <label><input class="sr-only" type="radio" name="trip" value="one-way" @checked($selectedTrip === 'one-way')><span>{{ __('One Way') }}</span></label>
    </fieldset>
    <fieldset class="cabin-options">
        <legend class="sr-only">{{ __('Cabin class') }}</legend>
        @foreach(['' => 'Any cabin', 'economy' => 'Economy', 'business' => 'Business', 'first' => 'First'] as $value => $label)
            <label><input class="sr-only" type="radio" name="cabin" value="{{ $value }}" @checked(old('cabin', request('cabin', '')) === $value)><span>{{ __($label) }}</span></label>
        @endforeach
    </fieldset>
    </div>
    <fieldset class="landing-route-fields" data-single-trip>
        <x-travel-field name="from" :label="__('From')" :value="request('from')" :options="['' => __('Departure airport')] + $airports" required />
        <button type="button" class="swap-airports" aria-label="{{ __('Swap departure and arrival airports') }}">⇄</button>
        <x-travel-field name="to" :label="__('To')" :value="request('to')" :options="['' => __('Arrival airport')] + $airports" required />
        <x-travel-field name="depart" :label="__('Departure')" :value="request('depart')" type="date" required />
        <div class="return-date" @if($selectedTrip !== 'round-trip') hidden @endif><x-travel-field name="return" :label="__('Return')" :value="request('return')" type="date" :required="$selectedTrip === 'round-trip'" :disabled="$selectedTrip !== 'round-trip'" /></div>
    </fieldset>
    <fieldset data-multi-trip hidden disabled>
        <legend class="sr-only">{{ __('Multi-city') }}</legend>
        @foreach(range(0, 4) as $index)
            <fieldset class="landing-leg" data-trip-leg @if($index > 1) hidden disabled @endif>
                <legend>{{ __('Flight :number', ['number' => $index + 1]) }}</legend>
                <x-travel-field :name="'legs['.$index.'][from]'" :label="__('From')" :options="['' => __('Departure airport')] + $airports" required />
                <x-travel-field :name="'legs['.$index.'][to]'" :label="__('To')" :options="['' => __('Arrival airport')] + $airports" required />
                <x-travel-field :name="'legs['.$index.'][depart]'" :label="__('Departure')" type="date" required />
            </fieldset>
        @endforeach
        <div class="landing-leg-actions">
            <button type="button" data-add-leg>+ {{ __('Add flight') }}</button>
            <button type="button" data-remove-leg hidden>{{ __('Remove last flight') }}</button>
        </div>
    </fieldset>
    <div class="landing-search-actions">
        <details class="landing-travelers">
            <summary><span>{{ __('Travelers') }}</span><strong data-traveler-summary>{{ __('1 traveler') }}</strong></summary>
            <div class="traveler-popover">
                @foreach(['adults' => 'Adults', 'youth' => 'Youth (12-16)', 'children' => 'Children (2-12)', 'infants' => 'Infants (under 2)'] as $name => $label)
                    @php($minimum = $name === 'adults' ? 1 : 0)
                    <div class="traveler-counter" data-traveler-counter>
                        <label for="traveler-{{ $name }}">{{ __($label) }}</label>
                        <div class="traveler-stepper">
                            <button type="button" data-step="-1" aria-label="{{ __('Decrease :group', ['group' => __($label)]) }}">−</button>
                            <input id="traveler-{{ $name }}" type="number" name="{{ $name }}" value="{{ old($name, request($name, $minimum)) }}" min="{{ $minimum }}" max="{{ $name === 'infants' ? 0 : 9 }}" readonly>
                            <button type="button" data-step="1" aria-label="{{ __('Increase :group', ['group' => __($label)]) }}">+</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
        <button class="button search-submit">{{ __('Search Flights') }} <span aria-hidden="true">↗</span></button>
    </div>
</form>
