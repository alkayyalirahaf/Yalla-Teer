<?php

namespace App\Http\Controllers\AirlineAdmin;

use App\Models\Booking;
use App\Models\Seat;
use App\Services\BookingFinalizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController
{
    public function index(Request $request): View
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
        ]);

        $bookings = Booking::query()
            ->whereHas('bookingFlights.flight', fn ($query) => $query->where('airline_id', $airlineId))
            ->with(['customer.user'])
            ->withCount(['bookingFlights as airline_flights_count' => fn ($query) => $query
                ->whereHas('flight', fn ($flightQuery) => $flightQuery->where('airline_id', $airlineId))])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('code', 'like', '%'.$search.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('bookings.created_at')
            ->paginate(5)
            ->withQueryString();

        return view('airline.bookings.index', compact('bookings', 'filters'));
    }

    public function show(Booking $booking): View
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;
        $this->authorizeAirlineBooking($booking, $airlineId);

        $booking->load([
            'customer.user',
            'passengers',
            'payments',
            'bookingFlights' => fn ($query) => $query
                ->whereHas('flight', fn ($flightQuery) => $flightQuery->where('airline_id', $airlineId))
                ->with([
                    'flight.originAirport',
                    'flight.destinationAirport',
                    'flightClass',
                    'tickets.passenger',
                    'tickets.seat',
                ]),
        ]);

        return view('airline.bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;
        $bookingId = $booking->getKey();

        DB::transaction(function () use ($airlineId, $bookingId): void {
            $booking = Booking::query()
                ->whereKey($bookingId)
                ->whereHas('bookingFlights.flight', fn ($query) => $query->where('airline_id', $airlineId))
                ->lockForUpdate()
                ->first();

            abort_unless($booking !== null, 403);

            if ($booking->status !== 'cancelled') {
                $booking->update(['status' => 'cancelled']);
            }

            $bookingFlights = $booking->bookingFlights()
                ->whereHas('flight', fn ($query) => $query->where('airline_id', $airlineId))
                ->with('tickets:id,booking_flight_id,seat_id')
                ->get(['id', 'flight_class_id']);

            $flightClassIds = $bookingFlights->pluck('flight_class_id')->filter()->unique()->values();
            $bookedSeatIds = $bookingFlights
                ->flatMap(fn ($bookingFlight) => $bookingFlight->tickets->pluck('seat_id'))
                ->filter()
                ->unique()
                ->values();
            $hold = Cache::store('database')->get(BookingFinalizationService::holdKey($booking));
            $heldSeatIds = collect($hold['seats'] ?? [])->flatten()->filter()->unique()->values();

            $seats = Seat::query()
                ->whereIn('flight_class_id', $flightClassIds)
                ->where(function ($query) use ($bookedSeatIds, $heldSeatIds, $booking): void {
                    $query->where(fn ($booked) => $booked
                        ->where('status', 'booked')
                        ->whereIn('id', $bookedSeatIds))
                        ->orWhere(fn ($held) => $held
                            ->where('status', 'held')
                            ->where('held_by', $booking->customer_id)
                            ->whereIn('id', $heldSeatIds));
                })
                ->lockForUpdate()
                ->get();

            foreach ($seats as $seat) {
                $seat->update([
                    'status' => 'available',
                    'held_by' => null,
                    'held_until' => null,
                ]);
            }

            Cache::store('database')->forget(BookingFinalizationService::holdKey($booking));
        }, 3);

        return redirect()
            ->route('airline.booking', $booking)
            ->with('success', 'Booking cancelled and seats released.');
    }

    private function authorizeAirlineBooking(Booking $booking, int $airlineId): void
    {
        abort_unless(
            $booking->bookingFlights()
                ->whereHas('flight', fn ($query) => $query->where('airline_id', $airlineId))
                ->exists(),
            403,
        );
    }
}
