@extends(($admin ?? false) ? 'super-admin.management-layout' : 'layouts.app', ($admin ?? false) ? [] : ['area' => 'my'])
@section('title', ($admin ?? false) ? __('Bookings') : __('My bookings'))
@section('admin-title', __('Bookings'))
@section(($admin ?? false) ? 'admin-content' : 'content')
<header class="workspace-heading"><h1>{{ ($admin ?? false) ? __('Bookings') : __('My bookings') }}</h1>
@if(!($admin ?? false))
    <div class="actions"><a class="button secondary" href="{{ route('booking.lookup') }}">{{ __('Look up a booking') }}</a><a class="button secondary" href="{{ route('my.profile') }}">{{ __('Traveler profile') }}</a></div>
@endif
</header>
<div @class(['table-responsive' => ($admin ?? false), 'panel table-wrap' => !($admin ?? false)])>
    <table @class(['table table-striped align-middle' => ($admin ?? false)])>
        <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Status') }}</th><th>{{ __('Total') }}</th><th>{{ __('Date booked') }}</th></tr></thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <th scope="row"><a href="{{ ($admin ?? false) ? route('admin.booking', $booking->id) : route('my.booking', $booking->code) }}">{{ $booking->code }}</a></th>
                    <td><span class="booking-status">{{ __(ucfirst($booking->status)) }}</span></td><td>JOD {{ $booking->total }}</td><td>{{ $booking->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('No bookings yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $bookings->links('super-admin.pagination') }}

@endsection
