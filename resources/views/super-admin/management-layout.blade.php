@extends('layouts.app')

@section('title', trim($__env->yieldContent('admin-title', 'Super Admin')))
@section('body-class', 'super-admin-body')
@section('navigation')@endsection
@section('footer-links')@endsection

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @php
        $adminNavigation = [
            ['Dashboard', 'admin.dashboard', '⌂'],
            ['Airlines', 'admin.airlines', '✈'],
            ['Airline Admins', 'admin.airline-admins', '♙'],
            ['Customers', 'admin.customers', '♧'],
            ['Destinations', 'admin.countries', '◎'],
            ['Credits', 'admin.credits', '$'],
            ['Airports', 'admin.airports', '⌖'],
            ['Flights', 'admin.flights', '⇄'],
            ['Bookings', 'admin.bookings', '▤'],
            ['Payments', 'admin.payments', '$'],
            ['Reviews', 'admin.reviews', '☆'],
            ['AI Conversations', 'admin.ai-conversations', '✧'],
            ['Notifications', 'admin.notifications', '◉'],
            ['Messages', 'admin.messages', '✉'],
        ];
    @endphp
    <div class="admin-frame">
        <button class="admin-menu-toggle" type="button" aria-expanded="false" aria-controls="admin-sidebar">☰ <span>Menu</span></button>
        <aside class="admin-sidebar" id="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('assets/YallaTeer-Logo-02.png') }}" alt="Yalla Teer"><span>SUPER ADMIN</span></a>
            <nav aria-label="Super Admin navigation">
                @foreach($adminNavigation as [$label, $routeName, $icon])
                    <a class="admin-nav-link {{ (request()->routeIs($routeName . '*') || ($routeName === 'admin.bookings' && request()->routeIs('admin.booking'))) ? 'active' : '' }}" href="{{ route($routeName) }}"><span class="admin-nav-icon" aria-hidden="true">{{ $icon }}</span>{{ $label }}</a>
                @endforeach
            </nav>
        </aside>
        <main class="admin-main">
            <header class="admin-topbar">
                <span class="admin-topbar-location">Yalla Teer <span class="admin-topbar-separator" aria-hidden="true">/</span> <strong>{{ trim($__env->yieldContent('admin-title', 'Super Admin')) }}</strong></span>
                <nav class="admin-topbar-actions" aria-label="Admin account actions">
                    <a href="{{ route('home') }}">View website <span aria-hidden="true">&#8599;</span></a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Sign out</button>
                    </form>
                </nav>
            </header>
            <section class="admin-content">
                @yield('admin-content')
            </section>
        </main>
    </div>
    <script>
        document.querySelector('.admin-menu-toggle')?.addEventListener('click', function () {
            const open = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!open));
            document.querySelector('.admin-sidebar')?.classList.toggle('is-open', !open);
        });
    </script>
@endsection
