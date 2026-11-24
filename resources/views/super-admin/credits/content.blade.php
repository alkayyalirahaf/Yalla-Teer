
    <p>{{ __('Adjust customer credit with a reason. Every change is recorded in the transaction history.') }}</p>
    <x-management-filters>
<form method="GET" action="{{ route('admin.credits') }}" class="panel p-3 mb-3">
        <label class="form-label" for="search">{{ __('Customer name or email') }}</label>
        <input class="form-control mb-2" id="search" name="search" value="{{ $search }}" maxlength="100">
        <button class="btn btn-primary">{{ __('Search') }}</button>
    <a class="button secondary" href="{{ route('admin.credits') }}">{{ __('Reset') }}</a>
</form>
</x-management-filters>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Email') }}</th><th>{{ __('Credit balance') }}</th></tr></thead><tbody>
        @forelse($customers as $customer)
            <tr><td>{{ $customer->user->name }}</td><td>{{ $customer->user->email }}</td><td>{{ $customer->credit_balance }}
                <form method="POST" action="{{ route('admin.credits.update', $customer) }}" class="stack">
                    @csrf
                    <input type="hidden" name="operation_id" value="{{ \Illuminate\Support\Str::uuid() }}">
                    <x-travel-field name="action" label="Action" :options="['add' => 'Add credit', 'deduct' => 'Deduct credit', 'adjust' => 'Set balance']" />
                    <x-travel-field name="amount" label="Amount" type="number" min="0" max="99999999.99" step="0.01" required />
                    <x-travel-field name="reason" label="Reason" maxlength="200" required />
                    <button class="btn btn-primary" type="submit">{{ __('Update credit') }}</button>
                </form>
            </td></tr>
        @empty<tr><td colspan="3">{{ __('No customers found.') }}</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $customers->links('super-admin.pagination') }}
    <h2>{{ __('Transaction history') }}</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Balance after') }}</th><th>{{ __('Booking') }}</th><th>{{ __('Note') }}</th><th>{{ __('Date') }}</th></tr></thead><tbody>
        @forelse($transactions as $transaction)
            <tr><td>{{ $transaction->customer->user->name }}</td><td>{{ $transaction->type }}</td><td>{{ $transaction->amount }}</td><td>{{ $transaction->balance_after }}</td><td>{{ $transaction->booking?->code ?? '—' }}</td><td>{{ $transaction->note }}</td><td>{{ $transaction->created_at?->format('Y-m-d H:i') }}</td></tr>
        @empty<tr><td colspan="7">{{ __('No transactions found.') }}</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $transactions->links('super-admin.pagination') }}
