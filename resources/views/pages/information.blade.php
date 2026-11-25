@extends('layouts.app')

@section('title'){{ __($title) }}@endsection

@section('content')
<div class="prose-page">
    <h1>{{ __($title) }}</h1>
    @switch($page)
        @case('about')
            <article class="panel"><h2>{{ __('Our mission') }}</h2><p>{{ __('Yalla Teer brings flight search, fare comparison, and destination information together so travelers can make informed choices.') }}</p><h2>{{ __('Our team') }}</h2><p>{{ __('Our team builds tools for travelers, airline partners, and platform staff.') }}</p></article>
            @break
        @case('faq')
@foreach([
        'How do I search for a flight?' => 'Choose departure and arrival airports or cities, a travel date and the seats needed. You can filter by cabin and base fare and sort the results.',
        'How do I search for a round trip?' => 'Choose round trip and enter both dates. Select an outbound cabin, then select a return cabin from the matching results.',
        'Does selecting a flight reserve a seat?' => 'Search selection does not reserve seats. Availability and prices must be confirmed by the booking process.',
        'Where can I see baggage information?' => 'Flight details and comparison show the cabin and checked baggage weights recorded for each cabin.',
        'How many flights can I compare?' => 'Select two or three different flights in the search results.',
        'When can I submit a review?' => 'You can review a flight segment belonging to your own completed booking after that flight has completed. Each segment can be reviewed once.',
        'What are the cancellation and refund rules?' => 'Approved cancellation, refund and credit policies have not been supplied for publication. Contact the platform team for the applicable terms before booking.',
    ] as $question => $answer)
        <details class="panel mb-3"><summary>{{ __($question) }}</summary><p class="mt-2 mb-0">{{ __($answer) }}</p></details>
    @endforeach
            @break
        @case('terms')
<article class="panel">
        <p class="notice">{{ __('Full terms are awaiting approval and publication by the platform team.') }}</p>
        <h2 class="h4">{{ __('Flight information') }}</h2>
        <p>{{ __('Search results display recorded schedules, base fares, cabin baggage allowances and currently available seat counts. Selecting flights does not create a booking or guarantee a price.') }}</p>
        <h2 class="h4">{{ __('Booking, cancellation, refunds and credits') }}</h2>
        <p>{{ __('The team has not supplied approved policies for these services. This page does not establish cancellation deadlines, refund amounts, credit expiry rules or fees. Request the applicable terms before completing a booking.') }}</p>
        <h2 class="h4">{{ __('Travel tips') }}</h2>
        <p>{{ __('Assistant travel tips are general information. Confirm travel requirements and your itinerary with the relevant authorities and airline.') }}</p>
    </article>
            @break
        @case('privacy')
            <article class="panel stack">
                <section>
                    <h2>{{ __('Bookings') }}</h2>
                    <p>{{ __('We collect the passport and identity details you provide when they are needed to arrange a booking.') }}</p>
                </section>
                <section>
                    <h2>{{ __('Payments') }}</h2>
                    <p>{{ __('Card details are entered on Stripe Checkout. We never store your card details.') }}</p>
                </section>
                <section>
                    <h2>{{ __('Contact messages') }}</h2>
                    <p>{{ __('Messages sent through our contact form are saved so our team can respond.') }}</p>
                </section>
                <section>
                    <h2>{{ __('Search and AI') }}</h2>
                    <p>{{ __('AI features may process text you enter while searching or chatting. Passport photos are never stored.') }}</p>
                </section>
                <section>
                    <h2>{{ __('Account credits') }}</h2>
                    <p>{{ __('Your account credits and balance changes are kept in a transaction ledger.') }}</p>
                </section>
            </article>
            @break
        @case('contact')
            @if(\Illuminate\Support\Facades\Route::has('contact.store'))
            <form action="{{ route('contact.store') }}" method="POST" class="panel stack">
                @csrf
                <label class="field">
                    <span>{{ __('Name') }}</span>
                    <input name="name" value="{{ old('name') }}" maxlength="150" required>
                    @error('name')<small class="error">{{ $message }}</small>@enderror
                </label>
                <label class="field">
                    <span>{{ __('Email') }}</span>
                    <input name="email" type="email" value="{{ old('email') }}" maxlength="255" required>
                    @error('email')<small class="error">{{ $message }}</small>@enderror
                </label>
                <label class="field">
                    <span>{{ __('Topic') }}</span>
                    <select name="type" required>
                        <option value="question" @selected(old('type') === 'question')>{{ __('Question') }}</option>
                        <option value="suggestion" @selected(old('type') === 'suggestion')>{{ __('Suggestion') }}</option>
                        <option value="complaint" @selected(old('type') === 'complaint')>{{ __('Complaint') }}</option>
                    </select>
                    @error('type')<small class="error">{{ $message }}</small>@enderror
                </label>
                <label class="field">
                    <span>{{ __('Message') }}</span>
                    <textarea name="message" maxlength="5000" required>{{ old('message') }}</textarea>
                    @error('message')<small class="error">{{ $message }}</small>@enderror
                </label>
                <button class="button" type="submit">{{ __('Send message') }}</button>
            </form>
            @else
                <p class="notice" role="status">{{ __('The contact form is temporarily unavailable. Please try again later.') }}</p>
            @endif
            @break
    @endswitch
</div>
@endsection
