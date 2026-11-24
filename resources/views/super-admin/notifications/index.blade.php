@extends('super-admin.management-layout')
@section('admin-title', 'Notification logs')
@section('admin-content')
    <h1>Notification logs</h1>
    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.notifications') }}">
                <div class="col-md-4">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach(['pending', 'sent', 'failed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit">Apply filters</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.notifications') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </details>
    <div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Booking</th><th>Channel</th><th>Type</th><th>Scheduled</th><th>Sent</th><th>Status</th></tr></thead><tbody>
        @forelse($logs as $log)<tr><td>{{ $log->booking->code }}</td><td>{{ $log->channel }}</td><td>{{ $log->type }}</td><td>{{ $log->scheduled_at }}</td><td>{{ $log->sent_at ?: '—' }}</td><td>{{ $log->status }}</td></tr>
        @empty<tr><td colspan="6">No notification logs found.</td></tr>@endforelse
    </tbody></table></div>{{ $logs->links('super-admin.pagination') }}
@endsection