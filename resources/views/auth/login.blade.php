@extends('layouts.app')

@section('title'){{ __($title) }}@endsection

@section('content')
<div class="{{ request()->routeIs('login') ? 'account-auth' : 'narrow' }}">
    <div class="{{ request()->routeIs('login') ? 'account-auth__content' : '' }}">
    <h1>{{ request()->routeIs('login') ? __('Welcome back') : __($title) }}</h1>
    @php($loginAction = $action ?? (\Illuminate\Support\Facades\Route::has('login.store') ? route('login.store') : null))
    @if($loginAction)
    <form action="{{ $loginAction }}" method="POST" class="panel stack" onsubmit="if (this.dataset.submitting === 'true') return false; this.dataset.submitting = 'true'; const button = this.querySelector('button[type=submit]'); button.disabled = true; button.textContent = 'Signing in…';">
        @csrf
        <x-travel-field name="email" label="Email" type="email" autocomplete="username" required />
        <x-travel-field name="password" label="Password" type="password" autocomplete="current-password" required />
        <button class="button" type="submit">{{ __('Sign in') }}</button>
    </form>
    @else
        <p class="notice" role="status">{{ __('Sign-in is temporarily unavailable. Please try again later.') }}</p>
    @endif
    @if(request()->routeIs('login'))
        <p class="account-auth__switch">{{ __('New to Yalla Teer?') }} <a href="{{ route('register') }}">{{ __('Create an account') }}</a></p>
        <div class="actions account-auth__staff">
            <a href="{{ route('airline.login') }}">{{ __('Airline staff') }}</a>
            <a href="{{ route('admin.login') }}">{{ __('Platform staff') }}</a>
        </div>
    @endif
    </div>
    @if(request()->routeIs('login'))
        <img class="account-auth__photo account-auth__photo--boarding" src="{{ asset('assets/landing/login-boarding.jpg') }}" alt="">
    @endif
</div>
@endsection
