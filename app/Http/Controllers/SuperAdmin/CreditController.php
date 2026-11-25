<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CreditTransaction;
use App\Services\CreditService;

class CreditController
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->superAdmin, 403);
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = $data['search'] ?? '';

        $customers = Customer::with('user')
            ->when($search !== '', fn ($query) => $query->whereHas('user', fn ($users) => $users
                ->where(fn ($user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))))
            ->orderBy('id')->paginate(5, ['*'], 'customers_page')->withQueryString();

        $transactions = CreditTransaction::with('customer.user', 'booking')
            ->when($search !== '', fn ($query) => $query->whereHas('customer.user', fn ($users) => $users
                ->where(fn ($user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))))
            ->latest('id')->paginate(5, ['*'], 'history_page')->withQueryString();

        return view('super-admin.panel', array_merge(compact('customers', 'transactions', 'search'), ['area' => 'admin', 'screen' => 'credits', 'title' => 'Customer credits']));
    }

    public function update(Request $request, Customer $customer, CreditService $credits)
    {
        abort_unless($request->user()?->superAdmin, 403);
        $data = $request->validate([
            'action' => ['required', 'in:add,deduct,adjust'],
            'amount' => ['required', 'decimal:0,2', $request->input('action') === 'adjust' ? 'min:0' : 'gt:0', 'max:99999999.99'],
            'reason' => ['required', 'string', 'max:200'],
            'operation_id' => ['required', 'uuid'],
        ]);

        $actor = $request->user()->id;
        match ($data['action']) {
            'add' => $credits->addCredits($customer->id, (string) $data['amount'], $data['operation_id'], $data['reason'], $actor),
            'deduct' => $credits->deductCredits($customer->id, (string) $data['amount'], $data['operation_id'], $data['reason'], $actor),
            'adjust' => $credits->adjustBalance($customer->id, (string) $data['amount'], $data['operation_id'], $data['reason'], $actor),
        };

        return redirect()->route('admin.credits')->with('status', 'Credit balance updated.');
    }
}
