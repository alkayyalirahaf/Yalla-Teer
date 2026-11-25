<?php

namespace App\Http\Controllers\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


use App\Models\NotificationLog;

class NotificationLogController
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'sent', 'failed'])],
        ]);

        $logs = NotificationLog::with('booking.customer.user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('super-admin.notifications.index', compact('logs'));
    }
}
