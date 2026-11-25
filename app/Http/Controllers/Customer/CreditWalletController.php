<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CreditWalletController
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;
        abort_unless($customer !== null, 403);

        return view('customer.credits', [
            'title' => 'Credits Wallet',
            'customer' => $customer,
            'transactions' => $customer->creditTransactions()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(5),
        ]);
    }
}
