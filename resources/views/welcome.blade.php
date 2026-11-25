@extends('layouts.app')

@section('title', __('Find your next flight'))
@section('body-class', 'landing')
@section('display-font')
@endsection

@section('navigation')
    <div class="landing-nav-links">
        <a href="#flight-search">{{ __('Flights') }}</a>
        <a href="#destinations">{{ __('Destinations') }}</a>
        <a href="#ai-copilot">{{ __('AI Assistant') }}</a>
        <a href="#how-it-works">{{ __('How It Works') }}</a>
    </div>
    <div class="landing-nav-actions">

        <x-nav-account />
        <a class="button landing-nav-book" href="#flight-search">{{ __('Find a Flight') }}</a>
    </div>
@endsection

@php
    $catalog = json_decode(file_get_contents(resource_path('views/shared/destination-catalog.json')), true, flags: JSON_THROW_ON_ERROR);
@endphp

@section('content')
<section class="landing-hero" aria-labelledby="landing-title">
    <div class="landing-hero-image" aria-hidden="true">
        <video class="hero-video" muted loop playsinline preload="none" poster="{{ asset('assets/landing/catalog/jp.webp') }}">
            <source src="{{ asset('assets/landing/hero-travel.mp4') }}" type="video/mp4">
        </video>
    </div>
    <div class="landing-wrap hero-copy">
        <h1 id="landing-title">{{ __('Your next flight') }} <span>{{ __('starts here.') }}</span></h1>
        <p>{{ __('Search flights and compare fares, or plan your trip with our AI assistant.') }}</p>
        <div class="actions">
            <a class="button" href="#flight-search">{{ __('Explore Flights') }} <span aria-hidden="true">↗</span></a>
            <a class="button secondary" href="#ai-copilot">{{ __('Meet Your AI Copilot') }}</a>
        </div>
    </div>
    <div class="landing-wrap reservation-tools"><x-landing-travel-tools /></div>
</section>

<div class="landing-journey">

    <section id="destinations" class="destination-showcase" aria-labelledby="destination-showcase-title">
        <div class="landing-wrap showcase-heading">
            <h2 id="destination-showcase-title">{{ __('See the world') }}</h2>
            <a class="destination-directory-link" href="{{ route('destinations') }}">
                {{ __('Browse all destinations') }} <span aria-hidden="true">&#8599;</span>
            </a>
        </div>
        <div class="destination-track" data-destination-track tabindex="0" role="region" aria-labelledby="destination-showcase-title">
            @foreach(collect($catalog)->pluck('places')->collapse()->keyBy('code')->only(['JP', 'TH', 'LOND', 'PARI', 'AG', 'KY', 'GD', 'KN', 'VC', 'ITO', 'LIR', 'RTB', 'OAX', 'PXM', 'TUS', 'PSP']) as $place)
                <article class="destination-slide">
                    <img src="{{ asset('assets/landing/catalog/' . strtolower($place['code']) . '.webp') }}" alt="" draggable="false" width="640" height="425" loading="lazy" decoding="async">
                    <span class="destination-slide-copy">
                        <span>{{ $place['code'] }}</span>
                        <strong>{{ __($place['name']) }}</strong>
                    </span>
                </article>
            @endforeach
        </div>
    </section>

    <section class="landing-wrap landing-section landing-split" id="ai-copilot" data-reveal>
        <div>
            <h2>{{ __('A world of possibilities.') }} <em>{{ __('One smart conversation.') }}</em></h2>
            <p>{{ __("Tell your AI copilot where you'd like to go. It can help you find flights, compare cabin options, and explore destination tips before you book.") }}</p>
            <a class="button" href="{{ route('assistant') }}">{{ __('Start Planning with AI') }} ↗</a>
        </div>
        <div class="copilot-preview">
            <div class="preview-header"><strong>{{ __('YallaTeer Copilot') }}</strong><span>{{ __('Conversation preview') }}</span></div>
            <p class="chat-question" data-preview-question>{{ __('Help me find a flight to Istanbul.') }}</p>
            <p class="chat-answer" data-preview-answer aria-live="polite">{{ __('Where are you flying from? Share your dates and I can help you compare routes.') }}</p>
            <div class="prompt-list" aria-label="{{ __('Try an example prompt') }}">
                @foreach([
                    'Help me find a flight to Istanbul.' => 'Where are you flying from? Share your dates and I can help you compare routes.',
                    'Compare economy and business class.' => 'Compare the fare, baggage rules, and seat options for the same flight. What route are you considering?',
                    'When is the best season to visit Paris?' => 'Spring and autumn are good starting points for milder weather. What would you like to do in Paris?',
                ] as $prompt => $answer)
                    <button type="button" data-prompt="{{ __($answer) }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ __($prompt) }}</button>
                @endforeach
            </div>
        </div>
    </section>
</div>

<section class="landing-wrap landing-section" id="how-it-works" data-reveal>
    <h2>{{ __('From takeoff plans') }} <em>{{ __('to boarding pass.') }}</em></h2>
    <ol class="booking-steps">
        @foreach([
            ['search', 'Find your flight', 'Compare routes, schedules, and fares.'],
            ['seat', 'Choose your cabin and seat', 'Review fare options and available seats.'],
            ['card', 'Add passenger details and pay', 'Review your itinerary and passenger details before paying.'],
            ['ticket', 'Access your ticket', 'Find it in your bookings when you’re ready to travel.'],
        ] as [$icon, $heading, $description])
            <li>
                <span class="step-number">0{{ $loop->iteration }}</span>
                <x-landing-icon :name="$icon" />
                <h3>{{ __($heading) }}</h3>
                <p>{{ __($description) }}</p>
            </li>
        @endforeach
    </ol>
</section>

<section class="landing-wrap landing-section landing-split" id="cabins" data-reveal>
    <div>
        <h2>{{ __('Choose how') }} <em>{{ __('you fly.') }}</em></h2>
        <p>{{ __('Choose a cabin and try selecting a seat. Your actual seat is selected during booking.') }}</p>
        <fieldset class="cabin-options">
            <legend class="sr-only">{{ __('Preview a cabin class') }}</legend>
            @foreach(['Economy' => 6, 'Business' => 4, 'First' => 2] as $cabin => $columns)
                <label><input type="radio" name="cabin-preview" value="{{ strtolower($cabin) }}" class="sr-only" @checked($loop->first)><span>{{ __($cabin) }}</span></label>
            @endforeach
        </fieldset>
    </div>
    <div class="cabin-preview">
        @foreach(['Economy' => 6, 'Business' => 4, 'First' => 2] as $cabin => $columns)
            <figure data-cabin="{{ strtolower($cabin) }}" @if(!$loop->first) hidden @endif>
                <figcaption><strong>{{ __($cabin) }}</strong><span>{{ __('Illustrative seat map') }}</span></figcaption>
                <fieldset class="seat-grid" style="--seat-columns: {{ $columns }}" dir="ltr">
                    <legend class="sr-only">{{ __('Choose a seat') }}</legend>
                    @foreach(range(1, 4) as $row)
                        @foreach(range(1, $columns) as $seat)
                            @php($seatNumber = $row.chr(64 + $seat))
                            <label>
                                <input class="sr-only" type="radio" name="preview-seat-{{ strtolower($cabin) }}" value="{{ $seatNumber }}" @disabled($row === 2 && $seat === 1)>
                                <span class="seat">{{ $seatNumber }}</span>
                            </label>
                            @if($seat === $columns / 2)<span class="seat-aisle">{{ $row }}</span>@endif
                        @endforeach
                    @endforeach
                </fieldset>
                <ul class="preview-seat-legend">
                    <li>{{ __('Available') }}</li>
                    <li>{{ __('Selected') }}</li>
                    <li>{{ __('Unavailable') }}</li>
                </ul>
                <a class="landing-text-link" href="{{ route('flights.search', ['cabin' => strtolower($cabin)]) }}">{{ __('Compare :cabin flights', ['cabin' => app()->isLocale('ar') ? __($cabin) : strtolower($cabin)]) }} ↗</a>
            </figure>
        @endforeach
    </div>
</section>

@endsection

@section('footer-links')
    <nav class="landing-footer-links" aria-label="{{ __('Footer') }}">
        <section aria-labelledby="footer-travel">
            <h2 id="footer-travel">{{ __('Plan your trip') }}</h2>
            <ul>
                <li><a href="#flight-search">{{ __('Flights') }}</a></li>
                <li><a href="{{ route('destinations') }}">{{ __('Destinations') }}</a></li>
                <li><a href="{{ route('assistant') }}">{{ __('AI Assistant') }}</a></li>
            </ul>
        </section>
        <section aria-labelledby="footer-help">
            <h2 id="footer-help">{{ __('Help') }}</h2>
            <ul>
                <li><a href="{{ route('booking.lookup') }}">{{ __('Booking lookup') }}</a></li>
                <li><a href="{{ route('faq') }}">{{ __('FAQ') }}</a></li>
                <li><a href="{{ route('contact') }}">{{ __('Contact') }}</a></li>
            </ul>
        </section>
        <section aria-labelledby="footer-legal">
            <h2 id="footer-legal">{{ __('Legal') }}</h2>
            <ul>
                <li><a href="{{ route('terms') }}">{{ __('Terms') }}</a></li>
                <li><a href="{{ route('privacy') }}">{{ __('Privacy') }}</a></li>
            </ul>
        </section>
    </nav>
    <p class="landing-footer-copyright">© {{ now()->year }} Yalla Teer</p>
@endsection
