@extends('layouts.app')

@section('title', __('Travel assistant'))

@section('content')
<div class="assistant-chat">
    <details class="panel assistant-history">
        <summary>{{ __('Conversation history') }}</summary>
        <a class="button secondary" href="{{ route('my.assistant') }}">{{ __('+ New conversation') }}</a>
        <ul>
            @forelse($conversations as $entry)
                <li><a href="{{ route('my.assistant.show', $entry->id) }}" @if(($activeConversation->id ?? null) === $entry->id) aria-current="page" @endif>
                    <span dir="auto">{{ $entry->title }}</span>
                    <small>{{ $entry->created_at?->format('d M, H:i') }}</small>
                </a></li>
            @empty
                <li>{{ __('No saved conversations.') }}</li>
            @endforelse
        </ul>
        {{ $conversations->links('super-admin.pagination') }}
    </details>
    <section class="panel assistant-room" aria-labelledby="assistant-title">
        <header class="assistant-room-header">
            @if(is_file(public_path('assets/landing/assistant-jet.png')))
                <img src="{{ asset('assets/landing/assistant-jet.png') }}" width="72" height="56" alt="">
            @endif
            <h1 id="assistant-title">{{ __('Travel assistant') }}</h1>
        </header>
        <div class="assistant-messages">
            @isset($transcript)
                <div class="assistant-message assistant-message-user">
                    <strong>{{ __('You') }}</strong>
                    <div dir="auto">{{ $transcript['message'] }}</div>
                </div>
                <div class="assistant-message">
                    <strong>{{ __('Yalla Teer') }}</strong>
                    @foreach((array) $transcript['tips'] as $tip)
                        <div dir="auto">{{ $tip }}</div>
                    @endforeach
                </div>
                @include('customer.assistant.show')
            @else
                <div class="assistant-message">
                    <strong>{{ __('Yalla Teer') }}</strong>
                    <div>{{ __('Hi! Ask me for travel tips, help finding flights, or a comparison.') }}</div>
                </div>
            @endisset
        </div>
    @unless($available)
        <p class="notice">{{ __('AI integration is currently unavailable. You can still') }} <a href="{{ route('flights.search') }}">{{ __('search flights') }}</a> {{ __('and') }} <a href="{{ route('compare') }}">{{ __('compare flights') }}</a>.</p>
    @endunless
    <form method="POST" action="{{ route('my.assistant.store') }}" class="assistant-composer" data-assistant-composer>
        @csrf
        <fieldset @disabled(!$available)>
            <div class="assistant-modes" role="group" aria-label="{{ __('Assistant mode') }}">
                @foreach(['tips' => 'Travel tips', 'find' => 'Find flights', 'compare' => 'Compare flights'] as $value => $label)
                    <label><input type="radio" name="mode" value="{{ $value }}" @checked(old('mode', 'tips') === $value) required> <span>{{ __($label) }}</span></label>
                @endforeach
            </div>
            <details class="assistant-options" data-assistant-options="find"><summary>{{ __('Flight search filters') }}</summary>
                @include('flights.filters', ['optionalFilters' => true])
            </details>
            <details class="assistant-options" data-assistant-options="compare"><summary>{{ __('Flight IDs for comparison') }}</summary>
                @foreach([0, 1, 2] as $index)
                    <label class="form-label" for="flight-{{ $index }}">{{ __('Flight') }} {{ $index + 1 }} @if($index === 2)(optional)@endif</label>
                    <input class="input-control mb-2" type="number" min="1" id="flight-{{ $index }}" name="flights[]" value="{{ old('flights.'.$index) }}">
                @endforeach
            </details>
            <div class="assistant-input">
                <label class="sr-only" for="message">{{ __('Travel question') }}</label>
                <textarea class="input-control" id="message" name="message" placeholder="{{ __('Ask about your trip…') }}" aria-describedby="assistant-privacy" maxlength="2000" rows="2" required>{{ old('message') }}</textarea>
                <button class="button" type="submit" aria-label="{{ __('Send message') }}" title="{{ __('Send message') }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3.3 3.2a.8.8 0 0 0-1.1 1l3.5 7.8-3.5 7.8a.8.8 0 0 0 1.1 1l18.3-8.1a.8.8 0 0 0 0-1.4L3.3 3.2Z"/>
                    </svg>
                </button>
            </div>
            <small id="assistant-privacy">{{ __('Keep passport and payment details out of your message.') }}</small>
        </fieldset>
    </form>
    </section>
</div>
@endsection
