@extends('layouts.app')

@section('title'){{ __($title) }}@endsection

@section('content')
<div class="account-auth">
    <div class="account-auth__content">
    <h1>{{ __('Create your account') }}</h1>
    @if(\Illuminate\Support\Facades\Route::has('register.store'))
    <form action="{{ route('register.store') }}" method="POST" class="panel stack" onsubmit="if (this.dataset.submitting === 'true') return false; this.dataset.submitting = 'true'; const button = this.querySelector('button[type=submit]'); button.disabled = true; button.textContent = 'Creating account…';">
        @csrf
        <x-travel-field name="name" label="Full name" autocomplete="name" required />
        <x-travel-field name="email" label="Email" type="email" autocomplete="email" required />
        <x-travel-field name="phone" label="Phone" type="tel" autocomplete="tel" />
        <div>
            <x-travel-field name="password" label="Password" type="password" autocomplete="new-password" minlength="12" aria-describedby="register-password-hint" required />
            <p id="register-password-hint" class="account-auth__hint">{{ __('Use 12+ characters with uppercase and lowercase letters, a number, and a symbol.') }}</p>
        </div>
        <x-travel-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" minlength="12" required />
        <button class="button" type="submit">{{ __('Create account') }}</button>
    </form>
    @else
        <p class="notice" role="status">{{ __('Registration is temporarily unavailable. Please try again later.') }}</p>
    @endif
    <p class="account-auth__switch">{{ __('Already have an account?') }} <a href="{{ route('login') }}">{{ __('Sign in') }}</a></p>
    </div>
    <img class="account-auth__photo" src="{{ asset('assets/landing/wing.webp') }}" alt="">
</div>
@endsection
