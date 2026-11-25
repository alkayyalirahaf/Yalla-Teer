<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController
{
    public function index(Request $request)
    {
        $customers = Customer::with('user')
            ->withCount('bookings')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('super-admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load('user');
        $bookings = $customer->bookings()->latest()->paginate(5);

        return view('super-admin.customers.show', compact('customer', 'bookings'));
    }

    public function setBanStatus(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'is_banned' => ['required', 'boolean'],
        ]);

        $customer->forceFill(['is_banned' => (bool) $data['is_banned']])->save();

        return back()->with('success', $customer->is_banned ? 'Customer banned.' : 'Customer unbanned.');
    }
}
