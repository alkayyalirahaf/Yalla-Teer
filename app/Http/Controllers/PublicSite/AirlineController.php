<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Airline;
use App\Models\Review;
use Illuminate\View\View;

class AirlineController
{
    public function index(): View
    {
        $airlines = Airline::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $visibleReviews = Review::query()
            ->where('status', 'visible')
            ->whereHas('bookingFlight.flight.airline', fn ($query) => $query->where('status', 'active'))
            ->with(['customer.user', 'bookingFlight.flight.airline'])
            ->latest()
            ->get()
            ->groupBy(fn ($review) => $review->bookingFlight?->flight?->airline_id);

        $airlines->each(function (Airline $airline) use ($visibleReviews): void {
            $reviews = $visibleReviews->get($airline->id, collect());
            $airline->setAttribute('visible_reviews', $reviews);
            $airline->setAttribute('average_rating', $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : null);
            $airline->setAttribute('reviews_count', $reviews->count());
        });

        return view('pages.airlines', compact('airlines'));
    }
}
