<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactMessageController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['new', 'handled'])],
            'type' => ['nullable', Rule::in(['suggestion', 'question', 'complaint'])],
        ]);

        $messages = ContactMessage::with('customer.user')
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('message', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('super-admin.messages.index', compact('messages', 'filters'));
    }

    public function markHandled(ContactMessage $message)
    {
        $message->update(['status' => 'handled']);

        return back()->with('success', 'Message marked as handled.');
    }
}
