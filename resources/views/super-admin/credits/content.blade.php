    <p>Adjust customer credit with a reason. Every change is recorded in the transaction history.</p>
    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.credits') }}" class="row g-3 align-items-end">
                <div class="col-md-8 col-lg-9">
                    <label class="form-label" for="search">Customer name or email</label>
                    <input class="form-control" id="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Search name or email">
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit">Apply filters</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.credits') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </details>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Customer</th><th>Email</th><th>Credit balance and adjustment</th></tr></thead><tbody>
        @forelse($customers as $customer)
            <tr><td>{{ $customer->user->name }}</td><td>{{ $customer->user->email }}</td><td>{{ $customer->credit_balance }}
                <form method="POST" action="{{ route('admin.credits.update', $customer) }}" class="stack mt-2">
                    @csrf
                    <input type="hidden" name="operation_id" value="{{ \Illuminate\Support\Str::uuid() }}">
                    <div class="card border-0 bg-light"><div class="card-body py-2 px-3">
                        <p class="fw-semibold mb-2">Adjust credit</p>
                        <div class="d-flex flex-wrap gap-2 align-items-end">
                            <div><label class="form-label mb-1">Action</label><select class="form-select form-select-sm" name="action"><option value="add">Add credit</option><option value="deduct">Deduct credit</option><option value="adjust">Set balance</option></select></div>
                            <div><label class="form-label mb-1">Amount</label><input class="form-control form-control-sm" name="amount" type="number" min="0" max="99999999.99" step="0.01" required></div>
                            <div class="flex-grow-1"><label class="form-label mb-1">Reason</label><input class="form-control form-control-sm" name="reason" maxlength="200" required></div>
                            <div><button class="btn btn-primary btn-sm" type="submit">Update credit</button></div>
                        </div>
                    </div></div>
                </form>
            </td></tr>
        @empty<tr><td colspan="3">No customers found.</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $customers->links('pagination::bootstrap-5') }}
    <h2>Transaction history</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Customer</th><th>Type</th><th>Amount</th><th>Balance after</th><th>Booking</th><th>Note</th><th>Date</th></tr></thead><tbody>
        @forelse($transactions as $transaction)
            <tr><td>{{ $transaction->customer->user->name }}</td><td>{{ $transaction->type }}</td><td>{{ $transaction->amount }}</td><td>{{ $transaction->balance_after }}</td><td>{{ $transaction->booking?->code ?? '—' }}</td><td>{{ $transaction->note }}</td><td>{{ $transaction->created_at?->format('Y-m-d H:i') }}</td></tr>
        @empty<tr><td colspan="7">No transactions found.</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $transactions->links('pagination::bootstrap-5') }}