@extends('layouts.app')

@section('body-class', 'super-admin-body airline-admin-body')
@section('navigation')@endsection
@section('footer-links')@endsection

@section('content')
    @php
        $links = [
            ['Dashboard', 'airline.dashboard', 'Dashboard', ['airline.dashboard']],
            ['Flights', 'airline.flights', 'plane', ['airline.flights', 'airline.flight', 'airline.flights']],
            ['Create flight', 'airline.create', 'Create flight', ['airline.create']],
            ['Bookings', 'airline.bookings', 'ticket', ['airline.bookings', 'airline.booking']],
            ['Reviews', 'airline.reviews', 'Reviews', ['airline.reviews']],
        ];
    @endphp
    <div class="admin-frame">
        <button class="admin-menu-toggle" type="button" aria-expanded="false" aria-controls="airline-sidebar"><x-landing-icon name="menu" /> {{ __('Menu') }}</button>
        <aside class="admin-sidebar" id="airline-sidebar">
            <a class="admin-brand" href="{{ route('airline.dashboard') }}">
                <img src="{{ asset('assets/yallateer-wordmark.png') }}" alt="Yalla Teer">
                <span>{{ __('Airline desk') }}</span>
            </a>
            <nav aria-label="{{ __('Airline administration') }}">
                @foreach($links as [$label, $route, $icon, $activeRoutes])
                    <a @class(['admin-nav-link', 'active' => request()->routeIs(...$activeRoutes)]) href="{{ route($route) }}">
                        <span class="admin-nav-icon" aria-hidden="true"><x-landing-icon :name="$icon" /></span>{{ __($label) }}
                    </a>
                @endforeach
            </nav>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <strong>{{ trim($__env->yieldContent('title', 'Airline desk')) }}</strong>
                <nav class="admin-topbar-actions" aria-label="{{ __('Account actions') }}">
                    <x-theme-switch />
                    <a href="{{ route('home') }}">{{ __('View website') }} <span aria-hidden="true">&#8599;</span></a>
                    <form method="POST" action="{{ route('airline.logout') }}">
                        @csrf
                        <button type="submit">{{ __('Sign out') }}</button>
                    </form>
                </nav>
            </header>
            <div class="admin-content">
                @yield('flight-content')
            </div>
        </div>
    </div>
    <script>
        document.querySelector('[aria-controls="airline-sidebar"]').addEventListener('click', function () {
            const open = this.getAttribute('aria-expanded') !== 'true';
            this.setAttribute('aria-expanded', String(open));
            document.getElementById('airline-sidebar').classList.toggle('is-open', open);
        });
    </script>
@endsection
