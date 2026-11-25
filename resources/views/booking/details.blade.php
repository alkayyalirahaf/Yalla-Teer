@extends(($admin ?? false) ? 'super-admin.management-layout' : 'layouts.app', ($admin ?? false) ? [] : ['area' => 'my'])
@section('title', $title)
@section('admin-title', $title)
@section('page-status')
    @unless(request()->routeIs('book.confirmation') && session('status') === 'Payment confirmed. Your booking is being finalized.')
        @parent
    @endunless
@endsection
@section(($admin ?? false) ? 'admin-content' : 'content')
@php
    $isReview = request()->routeIs('book.review');
    $isConfirmation = request()->routeIs('book.confirmation');
    $canPay = !($admin ?? false) && $booking->status === 'pending' && !$paid && $hold;
@endphp
<div @class(['stack', 'booking-details' => !($admin ?? false), 'booking-review' => $isReview])>
    <header class="booking-details-heading">
        <div>
            @unless($admin ?? false)
                <a class="selection-back" href="{{ route('my.bookings') }}">{{ __('← Back to my bookings') }}</a>
            @endunless
            <h1 @class(['flight-page-title' => !($admin ?? false)])>{{ __($title) }}</h1>
            <p><span>{{ $booking->code }}</span> <strong @class(['booking-status', 'booking-status--confirmed' => $booking->status === 'confirmed'])>{{ __(ucfirst($booking->status)) }}</strong></p>
        </div>
    </header>
    @if($isConfirmation && $booking->status === 'confirmed')
        <x-toast type="success">{{ __('Your booking is confirmed. View your tickets below.') }}</x-toast>
    @endif
    @if($paid && $booking->status !== 'confirmed' && $booking->status !== 'completed')
        <x-toast>{{ __('Payment received. Contact support to review this booking before paying again.') }}</x-toast>
    @endif
    @if($hold)
        <p @class(['notice' => !$isReview, 'booking-review-hold' => $isReview])>{{ __('Seats held until') }} <strong>{{ now()->setTimestamp($hold['expires'])->format('H:i T') }}</strong>.</p>
    @elseif($booking->status === 'pending' && !$paid && !($admin ?? false))
        <p class="notice">{{ __('Your seat hold has expired. Search again to choose available seats.') }}</p>
    @endif
    <div class="booking-details-grid">
    <section class="panel stack">
        <h2>{{ __('Itinerary') }}</h2>
        @foreach($booking->bookingFlights as $leg)
            <div class="booking-itinerary-leg">
                <p class="booking-leg-meta">{{ __(ucfirst($leg->direction)) }} · {{ $leg->flight->flight_number }}</p>
                <h3>{{ $leg->flight->originAirport->code }} → {{ $leg->flight->destinationAirport->code }}</h3>
                <p>{{ $leg->flight->departure_at->format('d M Y, H:i') }} · {{ __(ucfirst($leg->flightClass->class)) }}</p>
                @if(isset($hold['seats'][$leg->direction]))
                    <p>Held seats: {{ collect($hold['seats'][$leg->direction])->map(fn ($id) => $heldSeats->get($id))->implode(', ') }}</p>
                @endif
                @foreach($leg->tickets as $ticket)
                    <div class="booking-ticket-row">
                        <p><strong>{{ $ticket->passenger->first_name }} {{ $ticket->passenger->last_name }}</strong> <span>{{ __('Seat') }} {{ $ticket->seat?->seat_number ?? 'Unassigned' }} · Ticket {{ $ticket->ticket_number }}</span></p>
                        @if(!($admin ?? false) && $ticket->status === 'issued' && in_array($booking->status, ['confirmed', 'completed'], true))
                            <a class="button secondary" href="{{ route('my.ticket', $ticket->ticket_number) }}">{{ __('View ticket') }}<span class="sr-only"> for {{ $ticket->passenger->first_name }} {{ $ticket->passenger->last_name }}</span></a>
                        @endif
                    </div>
                @endforeach
                @if(!($admin ?? false) && $paid)
                    @php
                        $customerId = auth()->user()?->customer?->id;
                        $customerReview = $customerId ? $leg->reviews->firstWhere('customer_id', $customerId) : null;
                    @endphp
                    <div class="booking-ticket-row">
                        @if($customerReview)
                            <p><strong>{{ __('Your review') }}</strong> <span>{{ $customerReview->rating }}/5 - {{ __(ucfirst($customerReview->status)) }}</span></p>
                        @else
                            <p><strong>{{ __('Flight review') }}</strong> <span>{{ __('Rate this flight after payment.') }}</span></p>
                            <a class="button secondary" href="{{ route('my.reviews.create', $leg) }}">{{ __('Write a review') }}</a>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </section>
    <section class="panel booking-travelers">
        <h2>{{ __('Travelers') }}</h2>
        <ul>@foreach($booking->passengers as $passenger)<li>{{ $passenger->first_name }} {{ $passenger->last_name }}</li>@endforeach</ul>
        <p class="booking-total">{{ __('Total') }} <strong>JOD {{ $booking->total }}</strong></p>
        @if($isReview && $canPay)
            <a class="button booking-review-pay" href="{{ route('book.payment', ['booking_id' => $booking->id]) }}">{{ __('Continue to payment') }} <span aria-hidden="true">→</span></a>
        @endif
    </section>
    </div>
    @unless($admin ?? false)
        <div class="actions">
            @if($booking->status === 'pending' && !$paid)
                @if($canPay && !$isReview)
                    <a class="button" href="{{ route('book.payment', ['booking_id' => $booking->id]) }}">{{ __('Continue to payment') }}</a>
                @elseif(!$hold)
                    <a class="button" href="{{ route('flights.search') }}">{{ __('Search again') }}</a>
                @endif
                <form method="POST" action="{{ route('my.booking.cancel', $booking->code) }}">
                    @csrf
                    <button class="button secondary" type="submit">{{ __('Cancel unpaid booking') }}</button>
                </form>
            @elseif($booking->status === 'confirmed')
                <a href="{{ route('contact') }}">{{ __('Contact support about cancellation') }}</a>
            @endif
        </div>
    @endunless
</div>
@endsection
