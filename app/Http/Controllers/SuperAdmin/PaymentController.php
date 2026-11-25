<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaymentController
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,succeeded,failed'],
        ]);

        $payments = Payment::query()
            ->with(['booking.customer.user'])
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('stripe_payment_intent_id', 'like', '%'.$search.'%')
                        ->orWhereHas('booking', fn ($bookingQuery) => $bookingQuery
                            ->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('super-admin.payments.index', compact('payments', 'filters'));
    }
}
