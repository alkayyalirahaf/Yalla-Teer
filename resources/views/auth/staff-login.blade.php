@extends('layouts.app')

@section('title', $title)

@section('content')
<section class="staff-login panel narrow" aria-labelledby="staff-login-title">
    <a class="staff-login-brand" href="{{ route('home') }}">
        <img src="{{ asset('assets/yallateer-wordmark.png') }}" width="174" alt="Yalla Teer">
    </a>
    <h1 id="staff-login-title">{{ __($title) }}</h1>
    <form method="POST" action="{{ route($submitRoute) }}" class="stack">
        @csrf
        <x-travel-field name="email" label="Email address" type="email" autocomplete="username" required />
        <x-travel-field name="password" label="Password" type="password" autocomplete="current-password" required />

        @if($showRemember ?? false)
            <label class="staff-login-remember">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>{{ __('Remember me') }}</span>
            </label>
        @endif

        <button type="submit" class="button">{{ __('Sign in') }}</button>
    </form>
</section>
@endsection
