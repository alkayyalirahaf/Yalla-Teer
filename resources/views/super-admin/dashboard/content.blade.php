    <p class="eyebrow mb-2">{{ __('Platform overview') }}</p>
    <h1>{{ __('Platform Admin Dashboard') }}</h1>
    <div class="row g-3 mb-4">
        @foreach($statistics as $label => $count)
            <div class="col-6 col-xl-4"><article class="card card-body admin-stat-card h-100"><h2 class="h6">{{ __($label) }}</h2><p class="fs-3 mb-0">{{ number_format($count) }}</p></article></div>
        @endforeach
    </div>
    <div class="dashboard-ledger"><section>
    <h2 id="recent-bookings">{{ __('Recent bookings') }}</h2>
    <div class="table-wrap panel section"><table class="table table-striped">
        <thead><tr><th>{{ __('Booking') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created') }}</th></tr></thead><tbody>
        @forelse($recentBookings as $booking)
            <tr><td>{{ $booking->code }}</td><td>{{ $booking->customer->user->name }}</td><td>{{ $booking->status }}</td><td>{{ $booking->created_at?->format('Y-m-d H:i') }}</td></tr>
        @empty<tr><td colspan="4">{{ __('No bookings yet.') }}</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $recentBookings->links('super-admin.pagination') }}
    </section><section>
    <h2 id="recent-payments">{{ __('Recent payments') }}</h2>
    <div class="table-responsive"><table class="table table-striped">
        <thead><tr><th>{{ __('Payment') }}</th><th>{{ __('Booking') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created') }}</th></tr></thead><tbody>
        @forelse($recentPayments as $payment)
            <tr><td>{{ $payment->id }}</td><td>{{ $payment->booking->code }}</td><td>{{ $payment->amount }}</td><td>{{ $payment->status }}</td><td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td></tr>
        @empty<tr><td colspan="5">{{ __('No payments yet.') }}</td></tr>@endforelse
        </tbody>
    </table></div>

    {{ $recentPayments->links('super-admin.pagination') }}
    </section></div>
