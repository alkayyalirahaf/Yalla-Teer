<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;



class ReviewModerationController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['visible', 'hidden'])],
        ]);

        $reviews = Review::with(['customer.user', 'bookingFlight.flight.airline'])
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('customer.user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('bookingFlight.flight', fn ($flightQuery) => $flightQuery->where('flight_number', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('super-admin.reviews.index', compact('reviews', 'filters'));
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['visible', 'hidden'])],
        ]);

        $review->update(['status' => $data['status']]);

        return back()->with('success', 'Review status updated.');
    }
}
