<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <script src="{{ asset('assets/theme.js') }}"></script>
    <script src="https://mcp.figma.com/mcp/html-to-design/capture.js" async></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Travel') | Yalla Teer</title>
    <link rel="icon" href="{{ asset('assets/yallateer-wordmark.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:ital,wght@0,400..800;1,400..800&amp;family=Tajawal:wght@400;500;700;800&amp;display=swap">
    @section('display-font')@show
    <link rel="stylesheet" href="{{ asset('assets/theme-tokens.css') }}">
    @stack('framework-styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/theme-switch.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assistant-shortcut.css') }}">
</head>
<body class="@yield('body-class')">
    <a class="skip" href="#main">{{ __('Skip to content') }}</a>
    <header class="site-header">
        <div class="wrap bar">
            <a class="brand" href="{{ route('home') }}">
                <img src="{{ asset('assets/yallateer-wordmark.png') }}" width="174" height="59" alt="{{ __('YallaTeer home') }}">
            </a>
            <x-theme-switch />
            <button class="menu-toggle" type="button" aria-label="{{ __('Open navigation') }}" data-open-label="{{ __('Open navigation') }}" data-close-label="{{ __('Close navigation') }}" aria-expanded="false" aria-controls="primary-nav" hidden>
                <span class="menu-icon" aria-hidden="true"><span></span><span></span><span></span></span>
            </button>
            <nav id="primary-nav" aria-label="{{ __('Main navigation') }}">
                @section('navigation')
                <a href="{{ route('flights.search') }}">{{ __('Flights') }}</a>
                <a href="{{ route('destinations') }}">{{ __('Destinations') }}</a>
                <a href="{{ route('booking.lookup') }}">{{ __('Booking lookup') }}</a>
                @guest
                    <a href="{{ route('my.bookings') }}">{{ __('My bookings') }}</a>
                @endguest
                <x-nav-account />
                @show
            </nav>
        </div>
    </header>
    <main id="main" class="wrap main">
        @section('page-status')
            @if(session('status')) <x-toast>{{ session('status') }}</x-toast> @endif
            @if(session('success') && session('success') !== session('status')) <x-toast type="success">{{ session('success') }}</x-toast> @endif
            @if(session('error')) <x-toast type="error">{{ session('error') }}</x-toast> @endif
        @show
        @if(isset($errors) && $errors->any())
            <x-toast type="error"><strong>{{ __('Check the highlighted fields and try again.') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-toast>
        @endif
        @if(!empty($area))
            <nav @class(['subnav', 'account-subnav' => $area === 'my']) aria-label="{{ $area === 'my' ? 'Your account' : 'Management' }}">
                @php
                    $links = $area === 'my'
                        ? ['bookings' => ['/bookings', 'My Bookings'], 'credits' => ['/credits', 'Credits Wallet'], 'profile' => ['/profile', 'Profile']]
                        : ($navigation ?? []);
                @endphp
                @foreach($links as $key => [$path, $label])
                    @unless(str_contains($path, '{'))
                        <a @if(request()->routeIs("$area.$key") || ($area === 'my' && $key === 'bookings' && request()->routeIs('my.booking', 'book.confirmation', 'book.review'))) aria-current="page" @endif href="{{ route("$area.$key") }}">{{ __($label) }}</a>
                    @endunless
                @endforeach
            </nav>
        @endif
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="wrap bar">
            <a class="brand" href="{{ route('home') }}"><img src="{{ asset('assets/yallateer-wordmark.png') }}" width="174" height="59" alt="{{ __('YallaTeer home') }}"></a>
            @section('footer-links')
            <nav aria-label="{{ __('Footer') }}">
                @foreach(['about' => 'About', 'faq' => 'FAQ', 'terms' => 'Terms', 'privacy' => 'Privacy', 'contact' => 'Contact', 'assistant' => 'AI assistant'] as $name => $label)
                    <a href="{{ route($name) }}">{{ __($label) }}</a>
                @endforeach
            </nav>
            @show
        </div>
    </footer>
    <div class="site-toasts">@stack('toasts')</div>
    @unless(request()->is('admin', 'admin/*', 'airline', 'airline/*'))
        <x-assistant-shortcut />
    @endunless
</body>
</html>
