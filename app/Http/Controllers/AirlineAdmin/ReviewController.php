<?php

namespace App\Http\Controllers\AirlineAdmin;

use App\Models\Review;
use Illuminate\Contracts\View\View;

class ReviewController
{
    public function index(): View
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;

        $reviews = Review::query()
            ->whereHas('bookingFlight.flight', fn ($query) => $query->where('airline_id', $airlineId))
            ->with(['customer.user', 'bookingFlight.flight'])
            ->latest('reviews.created_at')
            ->paginate(5);

        return view('airline.reviews.index', compact('reviews'));
    }
}
