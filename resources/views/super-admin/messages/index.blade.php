@extends('super-admin.management-layout')
@section('admin-title', 'Contact messages')
@section('admin-content')
    <h1>Contact messages</h1>
    <details class="card mb-3" open>
        <summary class="card-header fw-semibold">Search and filters</summary>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.messages') }}" class="row g-3 align-items-end">
                <div class="col-md-4"><label class="form-label" for="search">Search sender, email, or message</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, or message text"></div>
                <div class="col-md-3"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All statuses</option><option value="new" @selected(($filters['status'] ?? '') === 'new')>New</option><option value="handled" @selected(($filters['status'] ?? '') === 'handled')>Handled</option></select></div>
                <div class="col-md-3"><label class="form-label" for="type">Type</label><select class="form-select" id="type" name="type"><option value="">All types</option><option value="suggestion" @selected(($filters['type'] ?? '') === 'suggestion')>Suggestion</option><option value="question" @selected(($filters['type'] ?? '') === 'question')>Question</option><option value="complaint" @selected(($filters['type'] ?? '') === 'complaint')>Complaint</option></select></div>
                <div class="col-md-2"><div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit">Apply filters</button><a class="btn btn-outline-secondary" href="{{ route('admin.messages') }}">Reset</a></div></div>
            </form>
        </div>
    </details>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Message</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->name }}</td>
                        <td>{{ $message->email }}</td>
                        <td>{{ ucfirst($message->type) }}</td>
                        <td style="min-width:250px">{{ $message->message }}</td>
                        <td>{{ $message->customer?->user?->name ?? 'Guest' }}</td>
                        <td>{{ $message->created_at }}</td>
                        <td>
                            @if($message->status === 'new')
                                <form method="POST" action="{{ route('admin.messages.handled', $message) }}" class="d-flex flex-wrap gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-primary" type="submit">Mark handled</button>
                                </form>
                            @else
                                <span class="badge text-bg-secondary">Handled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $messages->links('pagination::bootstrap-5') }}
@endsection
