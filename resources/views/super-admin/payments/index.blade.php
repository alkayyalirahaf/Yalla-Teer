@extends('super-admin.management-layout')

@section('admin-title', __('Payments'))

@section('admin-content')
    <h1>{{ __('Payments') }}</h1>

    <x-management-filters>
<form method="GET" action="{{ route('admin.payments') }}" class="row g-2 mb-3 align-items-end">
        <div class="col-md-6">
            <input aria-label="{{ __('Search booking code or Stripe payment intent') }}" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search booking code or Stripe payment intent') }}">
        </div>
        <div class="col-md-3">
            <select aria-label="{{ __('Status') }}" class="form-select" name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['pending', 'succeeded', 'failed'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(ucfirst($status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-primary" type="submit">{{ __('Filter') }}</button></div>
        <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('admin.payments') }}">{{ __('Clear') }}</a></div>
    </form>
</x-management-filters>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th scope="col">{{ __('Booking code') }}</th>
                    <th scope="col">{{ __('Customer') }}</th>
                    <th scope="col">{{ __('Amount') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col">{{ __('Stripe Payment Intent ID') }}</th>
                    <th scope="col">{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <th scope="row">{{ $payment->booking?->code ?? '—' }}</th>
                        <td>{{ $payment->booking?->customer?->user?->name ?? '—' }}</td>
                        <td>JOD {{ number_format((float) $payment->amount, 2) }}</td>
                        <td><span class="badge text-bg-secondary">{{ __(ucfirst($payment->status)) }}</span></td>
                        <td>{{ $payment->stripe_payment_intent_id ?? '—' }}</td>
                        <td>{{ $payment->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">{{ __('No payments found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $payments->links('super-admin.pagination') }}
@endsection
