@extends('layouts.app')
@section('title', __('Booking lookup'))
@section('content')
<section class="lookup-workspace panel narrow"><h1>{{ __('Booking lookup') }}</h1>
@if(auth()->user()?->customer)
    <form class="stack" method="POST" action="{{ route('booking.lookup.search') }}">
        @csrf
        <x-travel-field name="code" label="Booking reference" required maxlength="12" />
        <div><button class="button">{{ __('Find booking') }}</button></div>
    </form>
@else
    <p>{{ __('Sign in to find a booking in your account.') }}</p>
    <a class="button" href="{{ route('login') }}">{{ __('Sign in') }}</a>
@endif
</section>
@endsection
