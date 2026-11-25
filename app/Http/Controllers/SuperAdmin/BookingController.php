<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Booking;

class BookingController
{
    public function index()
    {
        $bookings = Booking::latest('id')->paginate(5);

        return view('booking.records', ['bookings' => $bookings, 'admin' => true]);
    }

    public function show(Booking $id)
    {
        $booking = $id->load(['bookingFlights.flight.originAirport', 'bookingFlights.flight.destinationAirport',
            'bookingFlights.flightClass', 'bookingFlights.tickets.seat', 'bookingFlights.tickets.passenger', 'passengers']);
        $paid = $booking->payments()->where('status', 'succeeded')->exists();

        return view('booking.details', [
            'booking' => $booking, 'title' => 'Booking details', 'admin' => true,
            'paid' => $paid, 'hold' => null, 'heldSeats' => collect(),
        ]);
    }
}
