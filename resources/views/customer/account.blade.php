@extends('layouts.app', ['area' => 'my'])

@php
    if (!app()->isLocal() || auth()->check()) {
        abort_unless(auth()->user()?->customer, 403);
    }

    $code = request()->route('code') ?? 'YTDEMO';
    $filter = request()->query('status', 'upcoming');
@endphp

@section('title'){{ __($title) }}@endsection

@section('content')
<h1>{{ __($title) }}</h1>
@unless($screen === 'review')<p class="notice">{{ __('Sample account. Changes made here are for preview only.') }}</p>@endunless
@switch($screen)
    @case('bookings')
        <nav class="subnav" aria-label="{{ __('Booking status') }}">
            @foreach(['upcoming', 'completed', 'cancelled'] as $status)
                <a href="{{ route('my.bookings', ['status' => $status]) }}" @if($filter === $status) aria-current="page" @endif>{{ __(ucfirst($status)) }}</a>
            @endforeach
        </nav>
        @if(in_array($filter, ['upcoming', 'completed'], true))
            <article class="panel"><h2><a href="{{ route('my.booking', $filter === 'completed' ? 'YTPAST' : 'YTDEMO') }}">{{ __('Amman → Dubai') }}</a></h2><p>{{ $filter === 'completed' ? 'Completed' : 'Upcoming' }} · YT 402</p></article>
        @else <p class="panel">{{ __('No bookings in this category.') }}</p> @endif
        @break
    @case('booking')
        <x-booking-summary :code="$code" />
        <div class="actions section">
            <a class="button" href="{{ route('my.review', $code) }}">{{ __('Write a review') }}</a>
            <a href="{{ route('contact') }}">{{ __('Get help with changes or cancellations') }}</a>
        </div>
        @break
    @case('review')
        <form class="panel narrow stack" method="POST" action="{{ route('my.reviews.store', $segment->id) }}">
            @csrf
            <h2>{{ $segment->flight->airline->name }} · {{ $segment->flight->flight_number }}</h2>
            <p>{{ __('Booking') }} {{ $segment->booking->code }}</p>
            <fieldset class="field star-rating-field">
                <legend>{{ __('Rating') }}</legend>
                <div class="star-rating" aria-label="{{ __('Rating out of 5 stars') }}">
                    @for($star = 5; $star >= 1; $star--)
                        <input type="radio" id="rating-{{ $star }}" name="rating" value="{{ $star }}" @checked((string) old('rating') === (string) $star) @required($star === 5)>
                        <label for="rating-{{ $star }}" title="{{ $star }} {{ $star === 1 ? __('star') : __('stars') }}"><span aria-hidden="true">&#9733;</span><span class="sr-only">{{ $star }} {{ $star === 1 ? __('star') : __('stars') }}</span></label>
                    @endfor
                </div>
                @error('rating')<small class="error">{{ $message }}</small>@enderror
            </fieldset>
            <x-travel-field name="comment" label="Comment" type="textarea" maxlength="5000" required />
            <p>{{ __('Submitted reviews are hidden until moderation.') }}</p>
            <button class="button" type="submit">{{ __('Submit review') }}</button>
        </form>
        @break
    @case('credits')
        <div class="panel"><p class="eyebrow">{{ __('Sample balance') }}</p><h2>JOD 25.00</h2></div>
        <section class="panel table-wrap section"><table><caption>{{ __('Credit activity') }}</caption>
            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th></tr></thead>
            <tbody><tr><td>10 Nov</td><td>{{ __('Sample credit') }}</td><td>+ JOD 30.00</td></tr><tr><td>11 Nov</td><td>{{ __('Sample redemption') }}</td><td>− JOD 5.00</td></tr></tbody>
        </table></section>
        @break
    @case('profile')
        <form method="post" data-preview-form class="panel narrow stack">
            <x-travel-field name="name" label="Name" value="Sample Traveler" required />
            <x-travel-field name="email" label="Email" type="email" value="traveler@example.test" required />
            <x-travel-field name="phone" label="Phone" type="tel" />
            <x-travel-field name="password" label="New password" type="password" autocomplete="new-password" disabled />
            <button class="button" disabled>{{ __('Preview changes') }}</button>
            <p data-preview-status role="status"></p>
        </form>
        @break
@endswitch
@endsection
