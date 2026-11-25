@extends('layouts.app')

@section('title', __('Passengers'))

@section('content')
<header class="passenger-heading">
    <a class="selection-back" href="{{ route('flights.search', session('dev3.search_selection.filters', [])) }}">{{ __('← Back to flight results') }}</a>
    <h1 class="flight-page-title">{{ __('Passengers') }}</h1>
</header>
<div class="passenger-layout">
    <section class="panel passenger-panel">
        <h2>{{ __('Travelers') }}</h2>
        <form class="stack" method="POST" action="{{ route('book.passengers.store') }}">
            @csrf
            <input type="hidden" name="selection_id" value="{{ $selectionId }}">
            @error('selection')<p class="notice error">{{ $message }}</p>@enderror
            @foreach($travelerTypes as $index => $type)
                <fieldset class="form-grid passenger-fields">
                    <legend>{{ __('Passenger') }} {{ $loop->iteration }} · {{ __(ucfirst($type)) }}</legend>
                    <x-passport-scan :passenger-index="$index" />
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][first_name]'" label="First / given name" :value="old('passengers.'.$index.'.first_name', $draft['passengers'][$index]['first_name'] ?? null)" maxlength="100" required />
                        @error('passengers.'.$index.'.first_name')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][last_name]'" label="Last / family name" :value="old('passengers.'.$index.'.last_name', $draft['passengers'][$index]['last_name'] ?? null)" maxlength="100" required />
                        @error('passengers.'.$index.'.last_name')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][date_of_birth]'" label="Date of birth" :value="old('passengers.'.$index.'.date_of_birth', $draft['passengers'][$index]['date_of_birth'] ?? null)" type="date" required />
                        @error('passengers.'.$index.'.date_of_birth')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][gender]'" label="Gender" :value="old('passengers.'.$index.'.gender', $draft['passengers'][$index]['gender'] ?? null)" :options="['' => 'Select', 'male' => 'Male', 'female' => 'Female']" required />
                        @error('passengers.'.$index.'.gender')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][nationality]'" label="Nationality" :value="old('passengers.'.$index.'.nationality', $draft['passengers'][$index]['nationality'] ?? null)" maxlength="60" required />
                        @error('passengers.'.$index.'.nationality')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][passport_number]'" label="Passport number" autocomplete="off" maxlength="20" required />
                        @error('passengers.'.$index.'.passport_number')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][passport_country]'" label="Issuing country" :value="old('passengers.'.$index.'.passport_country', $draft['passengers'][$index]['passport_country'] ?? null)" maxlength="60" required />
                        @error('passengers.'.$index.'.passport_country')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <x-travel-field :name="'passengers['.$index.'][passport_expiry]'" label="Passport expiry" :value="old('passengers.'.$index.'.passport_expiry', $draft['passengers'][$index]['passport_expiry'] ?? null)" :min="collect($cabins)->max(fn ($cabin) => $cabin->flight->departure_at->toDateString())" type="date" required />
                        @error('passengers.'.$index.'.passport_expiry')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="passenger-assistance">
                        <x-travel-field :name="'passengers['.$index.'][special_assistance]'" label="Special assistance (optional)" :value="old('passengers.'.$index.'.special_assistance', $draft['passengers'][$index]['special_assistance'] ?? null)" maxlength="255" />
                        @error('passengers.'.$index.'.special_assistance')<small class="error">{{ $message }}</small>@enderror
                    </div>
                </fieldset>
            @endforeach
            @error('passengers')<p class="notice error">{{ $message }}</p>@enderror
            <fieldset class="form-grid passenger-fields passenger-contact">
                <legend>{{ __('Booking contact') }}</legend>
                <x-travel-field name="contact_email" label="Email address" type="email" :value="$draft['contact_email'] ?? request()->user()->email" maxlength="255" required />
                <x-travel-field name="contact_phone" label="Phone number (optional)" type="tel" :value="$draft['contact_phone'] ?? null" maxlength="30" />
            </fieldset>
            <div class="passenger-actions">
                <button class="button" type="submit">{{ __('Continue to seats') }} <span aria-hidden="true">→</span></button>
            </div>
        </form>
    </section>
    <aside class="panel passenger-panel passenger-summary">
        <h2>{{ __('Trip summary') }}</h2>
        @foreach($cabins as $label => $cabin)
            <h3>{{ __($label) }}</h3>
            <dl>
                <div><dt>{{ __('Route') }}</dt><dd>{{ $cabin->flight->originAirport->code }} &rarr; {{ $cabin->flight->destinationAirport->code }}</dd></div>
                <div><dt>{{ __('Flight') }}</dt><dd>{{ $cabin->flight->flight_number }}</dd></div>
                <div><dt>{{ __('Departure') }}</dt><dd>{{ $cabin->flight->departure_at->format('d M Y, H:i') }}</dd></div>
                <div><dt>{{ __('Cabin') }}</dt><dd>{{ __(ucfirst($cabin->class)) }}</dd></div>
                <div><dt>{{ __('Current base fare per traveler') }}</dt><dd>JOD {{ $cabin->price }}</dd></div>
            </dl>
        @endforeach
    </aside>
</div>
@endsection
