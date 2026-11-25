<?php

namespace App\Http\Controllers\Customer;

use App\Models\BookingFlight;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewController
{
    public function forBooking(Request $request, string $code)
    {
        $customer = $request->user()?->customer;
        abort_unless($customer, 403);
        $booking = $customer->bookings()->where('code', $code)->with('payments')->firstOrFail();
        $segments = $booking->bookingFlights()->with('flight.airline')->get();
        $segment = $segments->first(fn ($segment) => $this->isPaid($booking)
            && ! $segment->reviews()->where('customer_id', $customer->id)->exists());
        if (! $segment) {
            throw ValidationException::withMessages(['review' => 'No paid, unreviewed flight is available in this booking.']);
        }

        return redirect()->route('my.reviews.create', $segment->id);
    }

    public function create(Request $request, int $bookingFlight)
    {
        $segment = $this->ownedSegment($request, $bookingFlight);
        $this->checkEligibility($segment);

        return view('customer.account', ['screen' => 'review', 'title' => 'Write a review', 'segment' => $segment]);
    }

    public function store(Request $request, int $bookingFlight)
    {
        $segment = $this->ownedSegment($request, $bookingFlight);
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        $customerId = $request->user()->customer->id;
        DB::transaction(function () use ($segment, $data, $customerId) {
            $booking = $segment->booking()->lockForUpdate()->firstOrFail();
            abort_unless((int) $booking->customer_id === (int) $customerId, 403);
            $segment = BookingFlight::whereKey($segment->id)->lockForUpdate()->firstOrFail();
            $segment->setRelation('booking', $booking);
            $segment->load('flight');
            $this->checkEligibility($segment);

            Review::create([
                'booking_flight_id' => $segment->id,
                'customer_id' => $booking->customer_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
                'status' => 'hidden',
            ]);
        });

        return redirect()->route('my.bookings')->with('status', 'Review submitted for moderation.');
    }

    private function ownedSegment(Request $request, int $id): BookingFlight
    {
        $customer = $request->user()?->customer;
        abort_unless($customer, 403);

        return BookingFlight::with('booking.payments', 'flight.airline')
            ->whereHas('booking', fn ($query) => $query->where('customer_id', $customer->id))
            ->findOrFail($id);
    }

    private function checkEligibility(BookingFlight $segment): void
    {
        if (! $this->isPaid($segment->booking)) {
            throw ValidationException::withMessages(['review' => 'Reviews are available after your booking is paid.']);
        }

        if ($segment->reviews()->where('customer_id', $segment->booking->customer_id)->exists()) {
            throw ValidationException::withMessages(['review' => 'You have already reviewed this flight segment.']);
        }
    }

    private function isPaid($booking): bool
    {
        if ($booking->relationLoaded('payments')) {
            return $booking->payments->contains(fn ($payment) => $payment->status === 'succeeded');
        }

        return $booking->payments()->where('status', 'succeeded')->exists();
    }
}
