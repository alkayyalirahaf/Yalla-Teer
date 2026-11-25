<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Models\Airline;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Flight;
use App\Models\Payment;

class DashboardController
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->superAdmin, 403);

        $queries = [
            'Customers' => Customer::query(),
            'Airlines' => Airline::query(),
            'Flights' => Flight::query(),
            'Bookings' => Booking::query(),
            'Payments' => Payment::query(),
            'Successful payments' => Payment::where('status', 'succeeded'),
        ];
        $recentBookings = Booking::with('customer.user')->latest('id')
            ->paginate(5, ['*'], 'bookings_page')->fragment('recent-bookings');
        $recentPayments = Payment::with('booking')->latest('id')
            ->paginate(5, ['*'], 'payments_page')->fragment('recent-payments');

        $statistics = array_map(fn ($query) => $query->count(), $queries);

        return view('super-admin.panel', array_merge(compact('statistics', 'recentBookings', 'recentPayments'), ['area' => 'admin', 'screen' => 'dashboard', 'title' => 'Platform Admin Dashboard']));
    }
}
