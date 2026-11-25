<?php

namespace App\Http\Controllers\AirlineAdmin;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\Review;
use App\Models\Seat;
use Illuminate\Contracts\View\View;

class DashboardController
{
    public function index(): View
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;

        $totalFlights = Flight::query()
            ->where('airline_id', $airlineId)
            ->count();

        $upcomingFlightsCount = Flight::query()
            ->where('airline_id', $airlineId)
            ->where('departure_at', '>=', now())
            ->where('status', 'scheduled')
            ->count();

        $airlineBookings = Booking::query()->whereHas('bookingFlights.flight', function ($query) use ($airlineId): void {
            $query->where('airline_id', $airlineId);
        });

        $totalBookings = (clone $airlineBookings)->count();

        $totalRevenue = (clone $airlineBookings)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereHas('payments', fn ($query) => $query->where('status', 'succeeded'))
            ->sum('amount_paid');

        $totalSeats = FlightClass::query()
            ->whereHas('flight', fn ($query) => $query->where('airline_id', $airlineId))
            ->sum('total_seats');

        $bookedSeats = Seat::query()
            ->where('status', 'booked')
            ->whereHas('flightClass.flight', fn ($query) => $query->where('airline_id', $airlineId))
            ->count();

        $seatOccupancyRate = $totalSeats > 0
            ? round($bookedSeats / $totalSeats * 100, 1)
            : 0;

        $averageRating = Review::query()
            ->whereHas('bookingFlight.flight', fn ($query) => $query->where('airline_id', $airlineId))
            ->avg('rating');

        $upcomingFlights = Flight::query()
            ->where('airline_id', $airlineId)
            ->where('departure_at', '>=', now())
            ->with([
                'originAirport',
                'destinationAirport',
                'flightClasses.seats' => fn ($query) => $query->where('status', 'available'),
            ])
            ->orderBy('departure_at')
            ->paginate(5, ['*'], 'upcoming_page')->fragment('upcoming-flights');

        $recentBookings = (clone $airlineBookings)
            ->with(['customer.user'])
            ->latest('bookings.created_at')
            ->paginate(5, ['*'], 'bookings_page')->fragment('recent-bookings');

        $recentReviews = Review::query()
            ->whereHas('bookingFlight.flight', fn ($query) => $query->where('airline_id', $airlineId))
            ->with(['customer.user', 'bookingFlight.flight'])
            ->latest('reviews.created_at')
            ->paginate(5, ['*'], 'reviews_page')->fragment('recent-reviews');

        return view('airline.dashboard', compact(
            'totalFlights',
            'upcomingFlightsCount',
            'totalBookings',
            'totalRevenue',
            'seatOccupancyRate',
            'averageRating',
            'upcomingFlights',
            'recentBookings',
            'recentReviews',
        ));
    }
}
