@extends('layouts.app')

@section('body-class', 'super-admin-body airline-admin-body')
@section('navigation')@endsection
@section('footer-links')@endsection

@section('content')
    @php
        $links = [
            ['Dashboard', 'airline.dashboard', '⌂', ['airline.dashboard']],
            ['Flights', 'airline.flights', '✈', ['airline.flights', 'airline.flight', 'airline.edit']],
            ['Create flight', 'airline.create', '+', ['airline.create']],
            ['Bookings', 'airline.bookings', '▤', ['airline.bookings', 'airline.booking']],
            ['Reviews', 'airline.reviews', '☆', ['airline.reviews']],
            ['Settings', 'airline.settings', '⚙', ['airline.settings']],
        ];
    @endphp
    <div class="admin-frame">
        <button class="admin-menu-toggle" type="button" aria-expanded="false" aria-controls="airline-sidebar">☰ Menu</button>
        <aside class="admin-sidebar" id="airline-sidebar">
            <a class="admin-brand" href="{{ route('airline.dashboard') }}">
                <img src="{{ asset('assets/YallaTeer-Logo-02.png') }}" alt="Yalla Teer">
                <span>Airline desk</span>
            </a>
            <nav aria-label="Airline administration">
                @foreach($links as [$label, $route, $icon, $activeRoutes])
                    <a @class(['admin-nav-link', 'active' => request()->routeIs(...$activeRoutes)]) href="{{ route($route) }}">
                        <span class="admin-nav-icon" aria-hidden="true">{{ $icon }}</span>{{ $label }}
                    </a>
                @endforeach
            </nav>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <strong>{{ trim($__env->yieldContent('title', 'Airline desk')) }}</strong>
                <nav class="admin-topbar-actions" aria-label="Account actions">
                    <a href="{{ route('home') }}">View website <span aria-hidden="true">&#8599;</span></a>
                    <form method="POST" action="{{ route('airline.logout') }}">
                        @csrf
                        <button type="submit">Sign out</button>
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
