@extends('layouts.app')

@section('title', __('Airlines'))
@section('body-class', 'landing')

@section('content')
<div class="landing-wrap destination-directory airlines-directory">
    <div class="airlines-page-head">
        <p class="directory-country">{{ __('Airline directory') }}</p>
        <h1>{{ __('Airlines') }}</h1>
        <p class="muted airlines-page-lead">{{ __('See available airlines, their ratings, and customer comments before you book.') }}</p>
    </div>

    @if($airlines->isNotEmpty())
        <div class="directory-grid airlines-grid">
            @foreach($airlines as $airline)
                <article class="panel directory-card destination-group airline-card">
                    <div class="airline-card-header">
                        <div class="airline-brand">
                            @if($airline->logo)
                                <img src="{{ asset('storage/' . $airline->logo) }}" alt="{{ $airline->name }} logo" class="airline-logo">
                            @else
                                <div class="airline-logo airline-logo-fallback" aria-hidden="true">{{ strtoupper(substr($airline->code ?: $airline->name, 0, 2)) }}</div>
                            @endif
                            <div class="airline-brand-copy">
                                <p class="directory-country">{{ __('Available airline') }}</p>
                                <h2 class="airline-name">{{ $airline->name }}</h2>
                                <p class="muted airline-meta">
                                    @if($airline->code)<span>{{ $airline->code }}</span>@endif
                                    @if($airline->code && $airline->country)<span aria-hidden="true">&middot;</span>@endif
                                    @if($airline->country)<span>{{ $airline->country }}</span>@endif
                                </p>
                            </div>
                        </div>

                        <div class="airline-rating-summary" aria-label="{{ __('Average rating') }}">
                            @if($airline->reviews_count > 0)
                                <div class="star-display" aria-hidden="true">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= round($airline->average_rating) ? 'star-filled' : 'star-empty' }}">&#9733;</span>
                                    @endfor
                                </div>
                                <p class="airline-rating-value"><strong>{{ number_format($airline->average_rating, 1) }}</strong><span class="muted"> / 5</span></p>
                                <p class="muted airline-rating-count">{{ $airline->reviews_count }} {{ $airline->reviews_count === 1 ? __('review') : __('reviews') }}</p>
                            @else
                                <p class="muted" style="margin:0;">{{ __('No reviews yet') }}</p>
                            @endif
                        </div>
                    </div>

                    <section class="section airline-comments" aria-label="{{ __('Customer comments') }}">
                        <div class="airline-comments-head">
                            <h3>{{ __('Customer comments') }}</h3>
                            @if($airline->reviews_count > 0)
                                <span class="muted">{{ __('Latest visible reviews') }}</span>
                            @endif
                        </div>

                        @forelse($airline->visible_reviews->take(5) as $review)
                            <article class="panel airline-comment">
                                <div class="airline-comment-head">
                                    <div>
                                        <strong class="airline-comment-author">{{ $review->customer?->user?->name ?? __('Customer') }}</strong>
                                        @if($review->bookingFlight?->flight?->flight_number)
                                            <p class="muted airline-comment-flight">{{ __('Flight') }} {{ $review->bookingFlight->flight->flight_number }}</p>
                                        @endif
                                    </div>
                                    <div class="airline-comment-meta">
                                        <div class="star-display star-display-sm" aria-label="{{ $review->rating }} {{ __('out of 5 stars') }}">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}" aria-hidden="true">&#9733;</span>
                                            @endfor
                                        </div>
                                        <span class="muted">{{ $review->created_at?->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                                <p class="airline-comment-text">{{ $review->comment ?: __('No comment provided.') }}</p>
                            </article>
                        @empty
                            <p class="muted">{{ __('No visible comments yet.') }}</p>
                        @endforelse
                    </section>
                </article>
            @endforeach
        </div>
    @else
        <div class="panel">
            <p class="muted" style="margin:0;">{{ __('No airlines are available right now.') }}</p>
        </div>
    @endif
</div>
@endsection
