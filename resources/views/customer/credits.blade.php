@extends('layouts.app', ['area' => 'my'])

@section('title', $title)

@section('content')
<header class="workspace-heading wallet-heading"><h1>{{ __($title) }}</h1>

<section class="wallet-balance" aria-labelledby="credit-balance-heading">
    <p class="eyebrow">{{ __('Current balance') }}</p>
    <h2 id="credit-balance-heading">JOD {{ number_format((float) $customer->credit_balance, 2) }}</h2>
</section></header>

<section class="panel table-wrap section">
    <table>
        <caption>{{ __('Credit activity') }}</caption>
        <thead>
            <tr><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Balance after') }}</th><th>{{ __('Note') }}</th></tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at?->format('d M Y H:i') }}</td>
                    <td>{{ str($transaction->type)->replace('_', ' ')->title() }}</td>
                    <td>JOD {{ number_format((float) $transaction->amount, 2) }}</td>
                    <td>JOD {{ number_format((float) $transaction->balance_after, 2) }}</td>
                    <td>{{ $transaction->note ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('No credit transactions yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<div class="section">{{ $transactions->links('super-admin.pagination') }}</div>
@endsection
