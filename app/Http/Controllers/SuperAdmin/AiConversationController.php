<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\AiConversation;
use Illuminate\Http\Request;

class AiConversationController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'in:find,compare,tips'],
        ]);

        $conversations = AiConversation::with('customer.user')
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhereHas('customer.user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('super-admin.ai-conversations.index', compact('conversations', 'filters'));
    }

    public function show(AiConversation $conversation)
    {
        $conversation->load(['customer.user', 'recommendations.flight.airline']);

        return view('super-admin.ai-conversations.show', compact('conversation'));
    }
}
