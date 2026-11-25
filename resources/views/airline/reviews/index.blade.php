@extends('airline-admin.flights.layout')

@section('title', __('Airline Reviews'))

@section('flight-content')
<div class="flight-card">
    <div><h1>{{ __('Reviews') }}</h1><p>{{ __('Customer feedback for your airline\'s flights.') }}</p></div>
</div>

<section class="panel table-wrap section">
    <table>
        <caption>{{ __('Flight reviews') }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('Flight') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Rating') }}</th>
                <th scope="col">{{ __('Comment') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
                <tr>
                    <th scope="row">
                        @if($review->bookingFlight?->flight)
                            <a href="{{ route('airline.flight', $review->bookingFlight->flight) }}">{{ $review->bookingFlight->flight->flight_number }}</a>
                        @else
                            —
                        @endif
                    </th>
                    <td>{{ $review->customer?->user?->name ?? '—' }}</td>
                    <td>{{ $review->rating }} / 5</td>
                    <td>{{ $review->comment ?: '—' }}</td>
                    <td><span class="badge">{{ __(ucfirst($review->status)) }}</span></td>
                    <td>{{ $review->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('No reviews found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{ $reviews->links('super-admin.pagination') }}
@endsection
