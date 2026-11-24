@extends('super-admin.management-layout')
@section('admin-title', __('Notification logs'))
@section('admin-content')
    <h1>{{ __('Notification logs') }}</h1>
    <x-management-filters>
<form class="row g-2 mb-3" method="GET" action="{{ route('admin.notifications') }}">
        <div class="col-md-4"><label class="form-label" for="status">{{ __('Status') }}</label><select class="form-select" id="status" name="status"><option value="">{{ __('All statuses') }}</option>@foreach(['pending', 'sent', 'failed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>@endforeach</select></div>
        <div class="col-auto d-flex align-items-end gap-2"><button class="btn btn-primary">{{ __('Filter') }}</button><a class="btn btn-outline-secondary" href="{{ route('admin.notifications') }}">{{ __('Clear') }}</a></div>
    </form>
</x-management-filters>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>{{ __('Booking') }}</th><th>{{ __('Channel') }}</th><th>{{ __('Type') }}</th><th>{{ __('Scheduled') }}</th><th>{{ __('Sent') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
        @forelse($logs as $log)<tr><td>{{ $log->booking->code }}</td><td>{{ $log->channel }}</td><td>{{ $log->type }}</td><td>{{ $log->scheduled_at }}</td><td>{{ $log->sent_at ?: '—' }}</td><td>{{ $log->status }}</td></tr>
        @empty<tr><td colspan="6">{{ __('No notification logs found.') }}</td></tr>@endforelse
    </tbody></table></div>{{ $logs->links('super-admin.pagination') }}
@endsection
