@extends('super-admin.management-layout')
@section('admin-title', __('Contact messages'))
@section('admin-content')
    <h1>{{ __('Contact messages') }}</h1>
    <x-management-filters>
<form method="GET" action="{{ route('admin.messages') }}" class="row g-2 mb-3">
        <div class="col-md-4"><label class="form-label" for="search">{{ __('Search sender, email, or message') }}</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Name, email, or message text') }}"></div>
        <div class="col-md-3"><label class="form-label" for="status">{{ __('Status') }}</label><select class="form-select" id="status" name="status"><option value="">{{ __('All statuses') }}</option><option value="new" @selected(($filters['status'] ?? '') === 'new')>{{ __('New') }}</option><option value="handled" @selected(($filters['status'] ?? '') === 'handled')>{{ __('Handled') }}</option></select></div>
        <div class="col-md-3"><label class="form-label" for="type">{{ __('Type') }}</label><select class="form-select" id="type" name="type"><option value="">{{ __('All types') }}</option><option value="suggestion" @selected(($filters['type'] ?? '') === 'suggestion')>{{ __('Suggestion') }}</option><option value="question" @selected(($filters['type'] ?? '') === 'question')>{{ __('Question') }}</option><option value="complaint" @selected(($filters['type'] ?? '') === 'complaint')>{{ __('Complaint') }}</option></select></div>
        <div class="col-md-2 d-flex align-items-end gap-2"><button class="btn btn-primary">{{ __('Apply') }}</button><a class="btn btn-outline-secondary" href="{{ route('admin.messages') }}">{{ __('Clear') }}</a></div>
    </form>
</x-management-filters>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ __('Sender') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Message') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->name }}</td>
                        <td>{{ $message->email }}</td>
                        <td>{{ $message->type }}</td>
                        <td><button class="btn btn-sm btn-outline-primary" type="button" data-show-message="{{ $loop->index }}">{{ __('Show message') }}</button></td>
                        <td>{{ $message->customer?->user?->name ?? 'Guest' }}</td>
                        <td>{{ $message->created_at }}</td>
                        <td>
                            @if($message->status === 'new')
                                <form method="POST" action="{{ route('admin.messages.handled', $message) }}">@csrf
                            @method('PATCH')<button class="btn btn-sm btn-primary">{{ __('Mark handled') }}</button></form>@else Handled
                                @endif
                        </td>
                </tr>@empty<tr>
                    <td colspan="7">{{ __('No messages found.') }}</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>{{ $messages->links('super-admin.pagination') }}
    <dialog class="message-dialog" data-message-dialog aria-labelledby="message-dialog-title">
        <div class="message-dialog-content">
            <header><h2 id="message-dialog-title">{{ __('Contact message') }}</h2><button class="btn btn-sm btn-outline-secondary" type="button" data-close-message aria-label="{{ __('Close') }}">×</button></header>
            <p data-message-body></p>
        </div>
    </dialog>
    <script>
        const messageDialog = document.querySelector('[data-message-dialog]');
        const messageBody = messageDialog.querySelector('[data-message-body]');
        const messages = @json($messages->getCollection()->pluck('message')->values());
        document.querySelectorAll('[data-show-message]').forEach(button => button.addEventListener('click', () => {
            messageBody.textContent = messages[Number(button.dataset.showMessage)] ?? '';
            messageDialog.showModal();
        }));
        messageDialog.querySelector('[data-close-message]').addEventListener('click', () => messageDialog.close());
        messageDialog.addEventListener('click', event => {
            if (event.target === messageDialog) messageDialog.close();
        });
    </script>
@endsection
