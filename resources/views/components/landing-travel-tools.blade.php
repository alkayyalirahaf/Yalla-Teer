@php
    $tools = [
        'book' => ['Book a flight', 'plane'],
        'manage' => ['Manage booking', 'card'],
        'status' => ['Flight status', 'clock'],
        'check-in' => ['Online check-in', 'ticket'],
    ];
    $mode = request()->query('status_mode');
    $active = in_array($mode, ['number', 'route'], true) ? 'status' : 'book';
@endphp
<section id="flight-search" class="travel-tools" data-travel-tools aria-label="{{ __('Travel tools') }}">
    <div class="travel-tools-tabs" role="tablist" aria-label="{{ __('Travel tools') }}">
        @foreach($tools as $key => [$label, $icon])
            <button id="travel-tab-{{ $key }}" type="button" role="tab" aria-controls="travel-panel-{{ $key }}"
                aria-selected="{{ $active === $key ? 'true' : 'false' }}" tabindex="{{ $active === $key ? '0' : '-1' }}">
                <x-landing-icon :name="$icon" />
                {{ __($label) }}
            </button>
        @endforeach
    </div>
    <div id="travel-panel-book" role="tabpanel" aria-labelledby="travel-tab-book" @if($active !== 'book') hidden @endif>
        <x-landing-search class="landing-search panel" />
    </div>
    <div id="travel-panel-manage" class="travel-tools-panel" role="tabpanel" aria-labelledby="travel-tab-manage" hidden>
        <p>{{ __('For the demo, use booking code YTDEMO and last name Traveler.') }}</p>
        <form method="post" data-preview-form data-lookup
            data-found-message="{{ __('Sample booking found.') }}" data-missing-message="{{ __('No matching sample booking.') }}">
            <div class="travel-tool-fields">
                <x-travel-field name="last_name" :label="__('Last name')" required />
                <x-travel-field name="code" :label="__('Booking code')" maxlength="12" required />
                <button class="button" disabled>{{ __('Find booking') }}</button>
            </div>
            <p data-preview-status role="status"></p>
            <a data-lookup-result href="{{ route('my.booking', 'YTDEMO') }}" hidden>{{ __('Open sample booking') }} &nearr;</a>
        </form>
    </div>
    <div id="travel-panel-status" class="travel-tools-panel" role="tabpanel" aria-labelledby="travel-tab-status" @if($active !== 'status') hidden @endif>
        <x-landing-flight-status :mode="$mode" />
    </div>
    <div id="travel-panel-check-in" class="travel-tools-panel" role="tabpanel" aria-labelledby="travel-tab-check-in" hidden>
        <p>{{ __('Online check-in is handled by the airline operating your flight. Use your booking details to find the airline.') }}</p>
        <a class="button secondary" href="{{ route('booking.lookup') }}">{{ __('Open booking lookup') }} &nearr;</a>
    </div>
</section>
