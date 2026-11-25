<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Airline;
use App\Models\AirlineAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AirlineAdminController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'airline_id' => ['nullable', 'integer', 'exists:airlines,id'],
        ]);

        $airlineAdmins = AirlineAdmin::with(['user', 'airline'])
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->whereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['airline_id'] ?? null, fn ($query, $airlineId) => $query->where('airline_id', $airlineId))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $airlines = Airline::orderBy('name')->get(['id', 'name']);

        return view('super-admin.airline-admins.index', compact('airlineAdmins', 'airlines', 'filters'));
    }

    public function create()
    {
        $airlines = Airline::orderBy('name')->get();

        return view('super-admin.airline-admins.create', compact('airlines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'airline_id' => ['required', 'exists:airlines,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            AirlineAdmin::create([
                'user_id' => $user->id,
                'airline_id' => $data['airline_id'],
                'phone' => $data['phone'] ?? null,
                'job_title' => $data['job_title'] ?? null,
            ]);
        });

        return redirect()->route('admin.airline-admins')->with('success', 'Airline admin created.');
    }

    public function edit(AirlineAdmin $airlineAdmin)
    {
        $airlineAdmin->load('user');
        $airlines = Airline::orderBy('name')->get();

        return view('super-admin.airline-admins.edit', compact('airlineAdmin', 'airlines'));
    }

    public function update(Request $request, AirlineAdmin $airlineAdmin)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($airlineAdmin->user_id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'airline_id' => ['required', 'exists:airlines,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($data, $airlineAdmin) {
            $airlineAdmin->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            if (!empty($data['password'])) {
                $airlineAdmin->user->update(['password' => $data['password']]);
            }

            $airlineAdmin->update([
                'airline_id' => $data['airline_id'],
                'phone' => $data['phone'] ?? null,
                'job_title' => $data['job_title'] ?? null,
            ]);
        });

        return redirect()->route('admin.airline-admins')->with('success', 'Airline admin updated.');
    }

    public function destroy(AirlineAdmin $airlineAdmin)
    {
        DB::transaction(function () use ($airlineAdmin) {
            $user = $airlineAdmin->user;
            $airlineAdmin->delete();

            if ($user && !$user->customer && !$user->superAdmin) {
                $user->delete();
            }
        });

        return redirect()->route('admin.airline-admins')->with('success', 'Airline admin deleted.');
    }
}
