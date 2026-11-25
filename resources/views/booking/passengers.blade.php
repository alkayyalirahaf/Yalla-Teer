<div class="passenger-layout">
    <div class="stack">
        <section class="panel passenger-panel" aria-labelledby="passenger-heading">
            <h2 id="passenger-heading">{{ __('Passenger 1') }}</h2>
            <p>{{ __('Enter sample details as they would appear on a passport.') }}</p>
            <fieldset class="form-grid">
                <legend>{{ __('Personal information') }}</legend>
                <x-travel-field name="title" label="Title (optional)" :options="['' => 'Select a title', 'Mr' => 'Mr', 'Ms' => 'Ms', 'Mrs' => 'Mrs', 'Dr' => 'Dr']" />
                <x-travel-field name="type" label="Passenger type" :options="['adult' => 'Adult', 'child' => 'Child', 'infant' => 'Infant']" />
                <x-travel-field name="first_name" label="First / given name" maxlength="100" autocomplete="off" />
                <x-travel-field name="last_name" label="Last / family name" maxlength="100" autocomplete="off" />
                <x-travel-field name="date_of_birth" label="Date of birth" type="date" :max="now()->toDateString()" />
                <x-travel-field name="gender" label="Gender" :options="['' => 'Select', 'male' => 'Male', 'female' => 'Female']" />
                <x-travel-field name="nationality" label="Nationality" maxlength="60" autocomplete="off" />
                <x-travel-field name="special_assistance" label="Special assistance (optional)" maxlength="255" autocomplete="off" />
            </fieldset>
            <fieldset class="form-grid">
                <legend>{{ __('Travel document') }}</legend>
                <x-travel-field name="passport_number" label="Passport number" maxlength="20" autocomplete="off" />
                <x-travel-field name="passport_country" label="Issuing country" maxlength="60" autocomplete="off" />
                <x-travel-field name="passport_expiry" label="Passport expiry" type="date" />
            </fieldset>
        </section>
        <section class="panel passenger-panel" aria-labelledby="booking-contact-heading">
            <h2 id="booking-contact-heading">{{ __('Booking contact') }}</h2>
            <div class="form-grid">
                <x-travel-field name="contact_email" label="Email address" type="email" maxlength="255" autocomplete="off" />
                <x-travel-field name="contact_phone" label="Phone number (optional)" type="tel" maxlength="30" autocomplete="off" />
            </div>
        </section>
    </div>
    <aside class="panel passenger-panel passenger-summary">
        <h2>{{ __('Trip summary') }}</h2>
        @if($flight)
            <dl>
                <div><dt>{{ __('Route') }}</dt><dd><bdi>{{ $flight['from'] }} &rarr; {{ $flight['to'] }}</bdi></dd></div>
                <div><dt>{{ __('Flight') }}</dt><dd>{{ $flight['number'] }}</dd></div>
                <div><dt>{{ __('Departure') }}</dt><dd>{{ $flight['departure'] }}</dd></div>
                @if($fare)
                    <div><dt>{{ __('Cabin') }}</dt><dd>{{ __(ucfirst($class)) }}</dd></div>
                    <div><dt>{{ __('Base fare per traveler') }}</dt><dd>JOD {{ $fare[0] }}</dd></div>
                @endif
            </dl>
        @else
            <p><a href="{{ route('flights.search') }}">{{ __('Find a flight') }}</a> {{ __('to see your itinerary.') }}</p>
        @endif
        <p>{{ __('This preview does not submit or save passenger information.') }}</p>
        <a class="button" href="{{ route('book.seats', $selection) }}">{{ __('Preview seat selection &rarr;') }}</a>
    </aside>
</div>
