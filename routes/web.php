<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Auth\AirlineAdminSessionController;
use App\Http\Controllers\Auth\CustomerSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Customer\AiConversationController;
use App\Http\Controllers\Customer\BookingFlowController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\BookingLookupController;
use App\Http\Controllers\Customer\SeatSelectionController;
use App\Http\Controllers\Customer\TicketController;
use App\Http\Controllers\Customer\TravelerProfileController;
use App\Http\Controllers\SuperAdmin\BookingController as SuperAdminBookingController;
use App\Http\Controllers\Customer\CreditWalletController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\PublicSite\CompareController;
use App\Http\Controllers\PublicSite\ContactMessageController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\InformationController;
use App\Http\Controllers\PublicFlightController;
use App\Http\Controllers\SuperAdmin\AirportController;
use App\Http\Controllers\SuperAdmin\CityController;
use App\Http\Controllers\SuperAdmin\CountryController;
use App\Http\Controllers\SuperAdmin\CreditController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\FlightController as AdminFlightController;
use App\Http\Controllers\SuperAdmin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\PublicSite\DestinationController;
use App\Http\Controllers\SuperAdmin\AirlineController;
use App\Http\Controllers\SuperAdmin\AirlineAdminController;
use App\Http\Controllers\SuperAdmin\CustomerController;
use App\Http\Controllers\SuperAdmin\ReviewModerationController;
use App\Http\Controllers\SuperAdmin\AiConversationController as AdminAiConversationController;
use App\Http\Controllers\SuperAdmin\NotificationLogController;
use App\Http\Controllers\SuperAdmin\ContactMessageController as AdminContactMessageController;





use App\Http\Controllers\AirlineAdmin\DashboardController as AirlineDashboardController;
use App\Http\Controllers\AirlineAdmin\FlightController as AirlineFlightController;
use App\Http\Controllers\AirlineAdmin\BookingController as AirlineBookingController;
use App\Http\Controllers\AirlineAdmin\ReviewController as AirlineReviewController;







Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations');

Route::post('/language/{locale}', function (Request $request, string $locale) {
    $request->session()->put('landing_locale', $locale);

    return $request->expectsJson() ? response()->noContent() : back(fallback: route('home'));
})->whereIn('locale', ['en', 'ar'])->name('home.language');

Route::redirect('/destinations/{country}', '/destinations');

Route::get('/flights', [PublicFlightController::class, 'index'])->name('flights.search');
Route::post('/flights/selection', [PublicFlightController::class, 'select'])->name('flights.selection');
Route::get('/flights/{flight}', [PublicFlightController::class, 'show'])->whereNumber('flight')->name('flights.show');
Route::get('/compare', [CompareController::class, 'index'])->name('compare');

Route::view('/booking-lookup', 'booking.lookup', ['title' => 'Booking Lookup'])
    ->name('booking.lookup');
Route::post('/booking-lookup', [BookingLookupController::class, 'search'])->middleware(['role.customer', 'throttle:10,1'])->name('booking.lookup.search');

Route::get('/assistant', [AiConversationController::class, 'index'])->middleware('role.customer')->name('assistant');

Route::get('/login', [CustomerSessionController::class, 'create'])->name('login');
Route::post('/login', [CustomerSessionController::class, 'store'])->middleware('throttle:auth-login')->name('login.store');
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
Route::post('/logout', [CustomerSessionController::class, 'destroy'])->middleware('auth')->name('logout');

$informationPages = [
    'about' => 'About Us',
    'privacy' => 'Privacy Policy',
    'contact' => 'Contact and Suggestions',
];

foreach ($informationPages as $page => $title) {
    Route::view("/{$page}", 'pages.information', compact('page', 'title'))
        ->name($page);
}
Route::get('/faq', [InformationController::class, 'faq'])->name('faq');
Route::get('/terms', [InformationController::class, 'terms'])->name('terms');
Route::post('/contact', [ContactMessageController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/book/passengers', [BookingFlowController::class, 'passengers'])->middleware('role.customer')->name('book.passengers');
Route::post('/book/passengers', [BookingFlowController::class, 'passengers'])->middleware('role.customer')->name('book.passengers.store');
Route::middleware('role.customer')->group(function () {
    Route::get('/book/seats', [SeatSelectionController::class, 'select'])->name('book.seats');
    Route::post('/book/seats', [SeatSelectionController::class, 'select'])->name('book.seats.store');
    Route::get('/book/review', [CustomerBookingController::class, 'review'])->name('book.review');
    Route::get('/book/confirmation', [CustomerBookingController::class, 'confirmation'])->name('book.confirmation');
});

Route::get('/book/payment', [PaymentController::class, 'show'])->middleware('role.customer')->name('book.payment');
Route::post('/book/payment/intent', [PaymentController::class, 'intent'])->middleware('role.customer')->name('book.payment.intent');
Route::get('/book/payment/return', [PaymentController::class, 'return'])->middleware('role.customer')->name('book.payment.return');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::prefix('my')->name('my.')->middleware('role.customer')->group(function () {
    Route::get('/bookings', [CustomerBookingController::class, 'index'])->name('bookings');
    Route::get('/tickets/{number}', [TicketController::class, 'show'])->name('ticket');
    Route::get('/bookings/{code}', [CustomerBookingController::class, 'show'])->name('booking');
    Route::post('/bookings/{code}/cancel', [CustomerBookingController::class, 'cancel'])->name('booking.cancel');

    Route::get('/bookings/{code}/review', [ReviewController::class, 'forBooking'])->name('review');

    Route::get('/credits', [CreditWalletController::class, 'index'])->name('credits');

    Route::get('/profile', [TravelerProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [TravelerProfileController::class, 'update'])->name('profile.update');

    Route::get('/booking-flights/{bookingFlight}/review', [ReviewController::class, 'create'])->whereNumber('bookingFlight')->name('reviews.create');
    Route::post('/booking-flights/{bookingFlight}/review', [ReviewController::class, 'store'])->whereNumber('bookingFlight')->name('reviews.store');
    Route::get('/assistant', [AiConversationController::class, 'index'])->name('assistant');
    Route::post('/assistant', [AiConversationController::class, 'store'])->middleware('throttle:10,1')->name('assistant.store');
    Route::get('/assistant/{conversation}', [AiConversationController::class, 'show'])->whereNumber('conversation')->name('assistant.show');
});

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:auth-login')
    ->name('admin.login.submit');

Route::get('/airline/login', [AirlineAdminSessionController::class, 'create'])->name('airline.login');
Route::post('/airline/login', [AirlineAdminSessionController::class, 'store'])->middleware('throttle:auth-login')->name('airline.login.submit');

$adminPages = [
    'dashboard' => ['', 'Dashboard'],
    'airlines' => ['/airlines', 'Airlines', ['Name', 'Airline code', 'Country', 'Status']],
    'airline-admins' => ['/airline-admins', 'Airline Admins', ['Name', 'Email', 'Airline', 'Job title']],
    'customers' => ['/customers', 'Customers', ['Name', 'Email', 'Phone', 'Balance', 'Bookings']],
    'countries' => ['/countries', 'Countries', ['Name', 'Country code', 'Currency', 'Language', 'Local apps']],
    'cities' => ['/cities', 'Cities', ['Name', 'Country', 'Transport', 'Tips', 'Best season']],
    'airports' => ['/airports', 'Airports', ['Name', 'IATA code', 'City']],
    'flights' => ['/flights', 'Flights', ['Flight number', 'Airline', 'Route', 'Departure', 'Status']],
    'bookings' => ['/bookings', 'Bookings', ['Code', 'Route', 'Status', 'Total']],
    'booking' => ['/bookings/{id}', 'Booking Details'],
    'payments' => ['/payments', 'Payments', ['Booking', 'Receipt', 'Amount', 'Status', 'Stripe ID', 'Date']],
    'credits' => ['/credits', 'Credits', ['Customer', 'Amount', 'Type', 'Date']],
    'reviews' => ['/reviews', 'Review Moderation', ['Customer', 'Flight', 'Rating', 'Comment', 'Status']],
    'ai-conversations' => ['/ai-conversations', 'AI Conversations', ['Customer', 'Subject', 'Date']],
    'notifications' => ['/notifications', 'Notification Logs', ['Channel', 'Type', 'Scheduled', 'Sent', 'Status']],
    'messages' => ['/messages', 'Messages', ['Type', 'Sender', 'Email', 'Message', 'Status']],
];

$airlinePages = [
    'dashboard' => ['', 'Dashboard'],
    'flights' => ['/flights', 'Flights List', ['Flight number', 'Route', 'Departure', 'Status']],
    'create' => ['/flights/create', 'Create Flight'],
    'edit' => ['/flights/{id}/edit', 'Edit Flight'],
    'flight' => ['/flights/{id}', 'Flight Details'],
    'bookings' => ['/bookings', 'Bookings', ['Code', 'Route', 'Status']],
    'booking' => ['/bookings/{id}', 'Booking Details'],
    'reviews' => ['/reviews', 'Reviews', ['Flight', 'Rating', 'Comment', 'Date']],
];

$registerPanelRoutes = function (string $area, string $view, array $navigation) {
    $middleware = $area === 'admin' ? 'role.super-admin' : 'role.airline-admin';
    Route::middleware($middleware)->group(function () use ($area, $view, $navigation) {
        foreach ($navigation as $screen => $page) {
            if ($area === 'admin' && in_array($screen, [
                'dashboard', 'countries', 'cities', 'airports', 'credits', 'bookings', 'booking',
                'airlines', 'airline-admins', 'customers', 'reviews', 'ai-conversations', 'notifications', 'messages',
                'flights', 'payments',
            ], true)) {
                continue;
            }

            if ($area === 'airline' && in_array($screen, [
                'dashboard', 'flights', 'create', 'edit', 'flight', 'bookings', 'booking', 'reviews',
            ], true)) {
                continue;
            }

            [$path, $title] = $page;

            Route::view("/{$area}{$path}", $view, [
                'area' => $area,
                'screen' => $screen,
                'title' => $title,
                'columns' => $page[2] ?? [],
                'navigation' => $navigation,
            ])->whereNumber('id')->name("{$area}.{$screen}");
        }
    });
};

$registerPanelRoutes('admin', 'super-admin.panel', $adminPages);
$registerPanelRoutes('airline', 'airline-admin.panel', $airlinePages);



Route::prefix('admin')->name('admin.')->middleware('role.super-admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [SuperAdminBookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{id}', [SuperAdminBookingController::class, 'show'])->whereNumber('id')->name('booking');
    Route::get('/credits', [CreditController::class, 'index'])->name('credits');
    Route::post('/credits/{customer}', [CreditController::class, 'update'])->whereNumber('customer')->name('credits.update');
    Route::get('/flights', [AdminFlightController::class, 'index'])->name('flights');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments');
    Route::resource('cities', CityController::class)->except('show')->names(['index' => 'cities']);

    Route::get('/airports', [AirportController::class, 'index'])->name('airports');
    Route::get('/airports/create', [AirportController::class, 'create'])->name('airports.create');
    Route::post('/airports', [AirportController::class, 'store'])->name('airports.store');
    Route::get('/airports/{airport}/edit', [AirportController::class, 'edit'])->whereNumber('airport')->name('airports.edit');
    Route::put('/airports/{airport}', [AirportController::class, 'update'])->whereNumber('airport')->name('airports.update');
    Route::delete('/airports/{airport}', [AirportController::class, 'destroy'])->whereNumber('airport')->name('airports.destroy');
    Route::get('/countries', [CountryController::class, 'index'])->name('countries');
    Route::get('/countries/create', [CountryController::class, 'create'])->name('countries.create');
    Route::post('/countries', [CountryController::class, 'store'])->name('countries.store');
    Route::get('/countries/{country}/edit', [CountryController::class, 'edit'])->whereNumber('country')->name('countries.edit');
    Route::put('/countries/{country}', [CountryController::class, 'update'])->whereNumber('country')->name('countries.update');
    Route::delete('/countries/{country}', [CountryController::class, 'destroy'])->whereNumber('country')->name('countries.destroy');




    // Airlines
    Route::get('/airlines', [AirlineController::class, 'index'])->name('airlines');
    Route::get('/airlines/create', [AirlineController::class, 'create'])->name('airlines.create');
    Route::post('/airlines', [AirlineController::class, 'store'])->name('airlines.store');
    Route::get('/airlines/{id}/edit', [AirlineController::class, 'edit'])->whereNumber('id')->name('airlines.edit');
    Route::put('/airlines/{id}', [AirlineController::class, 'update'])->whereNumber('id')->name('airlines.update');
    Route::delete('/airlines/{id}', [AirlineController::class, 'destroy'])->whereNumber('id')->name('airlines.destroy');
    Route::patch('/airlines/{id}/suspend', [AirlineController::class, 'suspend'])->whereNumber('id')->name('airlines.suspend');

    // Airline admins
    Route::get('/airline-admins', [AirlineAdminController::class, 'index'])->name('airline-admins');
    Route::get('/airline-admins/create', [AirlineAdminController::class, 'create'])->name('airline-admins.create');
    Route::post('/airline-admins', [AirlineAdminController::class, 'store'])->name('airline-admins.store');
    Route::get('/airline-admins/{airlineAdmin}/edit', [AirlineAdminController::class, 'edit'])->name('airline-admins.edit');
    Route::put('/airline-admins/{airlineAdmin}', [AirlineAdminController::class, 'update'])->name('airline-admins.update');
    Route::delete('/airline-admins/{airlineAdmin}', [AirlineAdminController::class, 'destroy'])->name('airline-admins.destroy');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::patch('/customers/{customer}/ban', [CustomerController::class, 'setBanStatus'])->name('customers.ban');

    // Review moderation
    Route::get('/reviews', [ReviewModerationController::class, 'index'])->name('reviews');
    Route::patch('/reviews/{review}', [ReviewModerationController::class, 'update'])->name('reviews.update');

    // AI conversations
    Route::get('/ai-conversations', [AdminAiConversationController::class, 'index'])->name('ai-conversations');
    Route::get('/ai-conversations/{conversation}', [AdminAiConversationController::class, 'show'])->name('ai-conversations.show');

    // Notification logs
    Route::get('/notifications', [NotificationLogController::class, 'index'])->name('notifications');

    // Contact messages
    Route::get('/messages', [AdminContactMessageController::class, 'index'])->name('messages');
    Route::patch('/messages/{message}/handled', [AdminContactMessageController::class, 'markHandled'])->name('messages.handled');
});




// 1. Exclude these screens from $registerPanelRoutes when $area === 'airline':
// ['flights', 'create', 'edit', 'flight']  (and later: 'dashboard', 'bookings', 'booking', 'reviews')

// 2. Airline Admin Routes (C0 - C6)
Route::prefix('airline')->name('airline.')->middleware('role.airline-admin')->group(function () {
    Route::post('/logout', [AirlineAdminSessionController::class, 'destroy'])->name('logout');

    // C1: Dashboard
    Route::get('/', [AirlineDashboardController::class, 'index'])->name('dashboard');

    // C2, C3, C4: Flights & Seat Inventory (Tested & Ready)
    Route::get('/flights', [AirlineFlightController::class, 'index'])->name('flights');
    Route::get('/flights/create', [AirlineFlightController::class, 'create'])->name('create');
    Route::post('/flights', [AirlineFlightController::class, 'store'])->name('store');
    Route::get('/flights/{flight}', [AirlineFlightController::class, 'show'])->whereNumber('flight')->name('flight');
    Route::get('/flights/{flight}/edit', [AirlineFlightController::class, 'edit'])->whereNumber('flight')->name('edit');
    Route::put('/flights/{flight}', [AirlineFlightController::class, 'update'])->whereNumber('flight')->name('update');
    Route::delete('/flights/{flight}', [AirlineFlightController::class, 'destroy'])->whereNumber('flight')->name('destroy');

    // C5a, C5b: Bookings & Cancellation
    Route::get('/bookings', [AirlineBookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{booking}', [AirlineBookingController::class, 'show'])->whereNumber('booking')->name('booking');
    Route::post('/bookings/{booking}/cancel', [AirlineBookingController::class, 'cancel'])->whereNumber('booking')->name('bookings.cancel');

    // C6: Reviews
    Route::get('/reviews', [AirlineReviewController::class, 'index'])->name('reviews');
});

use App\Http\Controllers\Customer\PassportScanController;

Route::post('/book/passport-scan', PassportScanController::class)
    ->middleware('throttle:10,1')
    ->name('book.passport-scan');