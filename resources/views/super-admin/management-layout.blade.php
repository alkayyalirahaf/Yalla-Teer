@extends('layouts.app')

@section('title', trim($__env->yieldContent('admin-title', 'Super Admin')))
@section('body-class', 'super-admin-body')
@section('navigation')@endsection
@section('footer-links')@endsection

@push('framework-styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $adminNavigation = [
            ['Dashboard', 'admin.dashboard', 'Dashboard'],
            ['Airlines', 'admin.airlines', 'plane'],
            ['Airline Admins', 'admin.airline-admins', 'Customers'],
            ['Customers', 'admin.customers', 'Customers'],
            ['Countries', 'admin.countries', 'Countries'],
            ['Credits', 'admin.credits', 'card'],
            ['Airports', 'admin.airports', 'Airports'],
            ['Flights', 'admin.flights', 'plane'],
            ['Bookings', 'admin.bookings', 'ticket'],
            ['Payments', 'admin.payments', 'card'],
            ['Reviews', 'admin.reviews', 'Reviews'],
            ['AI Conversations', 'admin.ai-conversations', 'AI Conversations'],
            ['Notifications', 'admin.notifications', 'Notifications'],
            ['Messages', 'admin.messages', 'Messages'],
        ];
    @endphp
    <div class="admin-frame">
        <button class="admin-menu-toggle" type="button" aria-expanded="false" aria-controls="admin-sidebar"><x-landing-icon name="menu" /> <span>{{ __('Menu') }}</span></button>
        <aside class="admin-sidebar" id="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('assets/yallateer-wordmark.png') }}" alt="Yalla Teer"><span>{{ __('SUPER ADMIN') }}</span></a>
            <nav aria-label="{{ __('Super Admin navigation') }}">
                @foreach($adminNavigation as [$label, $routeName, $icon])
                    <a class="admin-nav-link {{ (request()->routeIs($routeName . '*') || ($routeName === 'admin.bookings' && request()->routeIs('admin.booking'))) ? 'active' : '' }}" href="{{ route($routeName) }}"><span class="admin-nav-icon" aria-hidden="true"><x-landing-icon :name="$icon" /></span>{{ __($label) }}</a>
                @endforeach
            </nav>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <span class="admin-topbar-location">{{ __('Yalla Teer') }} <span class="admin-topbar-separator" aria-hidden="true">/</span> <strong>{{ trim($__env->yieldContent('admin-title', 'Super Admin')) }}</strong></span>
                <nav class="admin-topbar-actions" aria-label="{{ __('Admin account actions') }}">
                    <x-theme-switch />
                    <a href="{{ route('home') }}">{{ __('View website') }} <span aria-hidden="true">&#8599;</span></a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">{{ __('Sign out') }}</button>
                    </form>
                </nav>
            </header>
            <section class="admin-content">
                @yield('admin-content')
            </section>
        </div>
    </div>
    <script>
        document.querySelector('.admin-menu-toggle')?.addEventListener('click', function () {
            const open = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!open));
            document.querySelector('.admin-sidebar')?.classList.toggle('is-open', !open);
        });
    </script>
@endsection
