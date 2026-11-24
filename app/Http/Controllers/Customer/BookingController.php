<?php

namespace App\Http\Controllers\Customer;

use App\Models\Booking;
use App\Models\Seat;
use App\Services\BookingFinalizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BookingController
{
    public function index(Request $request)
    {
        $bookings = $request->user()->customer->bookings()->latest('id')->paginate(5);

        return view('booking.records', compact('bookings'));
    }

    public function show(Request $request, string $code)
    {
        return $this->details($request->user()->customer->bookings()->where('code', $code)->firstOrFail(), 'Booking details');
    }

    public function review(Request $request)
    {
        return $this->details($this->fromQuery($request), 'Review booking');
    }

    public function confirmation(Request $request)
    {
        return $this->details($this->fromQuery($request), 'Booking confirmation');
    }

    public function cancel(Request $request, string $code)
    {
        DB::transaction(function () use ($request, $code): void {
            $booking = $request->user()->customer->bookings()->where('code', $code)->lockForUpdate()->firstOrFail();
            abort_unless($booking->status === 'pending'
                && !$booking->payments()->where('status', 'succeeded')->exists(), 409, 'Contact support to change a paid booking.');

            $hold = Cache::store('database')->get(BookingFinalizationService::holdKey($booking));
            if ($hold) {
                Seat::whereKey(collect($hold['seats'])->flatten()->all())
                    ->where('status', 'held')->where('held_by', $booking->customer_id)
                    ->where('held_until', date('Y-m-d H:i:s', $hold['expires']))
                    ->update(['status' => 'available', 'held_by' => null, 'held_until' => null]);
                Cache::store('database')->forget(BookingFinalizationService::holdKey($booking));
            }

            $booking->update(['status' => 'cancelled']);
        }, 3);

        return redirect()->route('my.booking', $code)->with('status', 'Booking cancelled.');
    }

    private function fromQuery(Request $request): Booking
    {
        $id = filter_var($request->query('booking_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $request->user()->customer->bookings()->findOrFail($id);
    }

    private function details(Booking $booking, string $title)
    {
        $booking->load(['bookingFlights.flight.originAirport', 'bookingFlights.flight.destinationAirport',
            'bookingFlights.flightClass', 'bookingFlights.tickets.seat', 'bookingFlights.tickets.passenger', 'passengers']);
        $hold = Cache::store('database')->get(BookingFinalizationService::holdKey($booking));
        $paid = $booking->payments()->where('status', 'succeeded')->exists();
        $heldSeats = $hold ? Seat::whereKey(collect($hold['seats'])->flatten()->all())->pluck('seat_number', 'id') : collect();

        return view('booking.details', compact('booking', 'title', 'hold', 'paid', 'heldSeats'));
    }
}
