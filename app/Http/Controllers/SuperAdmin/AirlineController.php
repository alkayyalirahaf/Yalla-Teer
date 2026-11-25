<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Airline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AirlineController
{
    // Display airlines with search, status filter, and pagination
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['pending', 'active', 'suspended'])],
        ]);

        $query = Airline::query();

        // Search by airline name or code
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter airlines by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Retrieve the latest airlines with pagination
        $airlines = $query->latest()
            ->paginate(5)
            ->withQueryString();

        return view('super-admin.airlines.index', compact('airlines'));
    }

    // Display the form for creating a new airline
    public function create()
    {
        return view('super-admin.airlines.create', ['airline' => new Airline()]);
    }

    // Validate and store a new airline
    public function store(Request $request)
    {
        $request->merge([
            'name' => is_string($request->name) ? trim($request->name) : $request->name,
            'code' => is_string($request->code) ? strtoupper(trim($request->code)) : $request->code,
            'country' => is_string($request->country) ? trim($request->country) : $request->country,
        ]);

        // Validate the submitted airline data
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:3|unique:airlines,code',
            'country' => 'required|string|max:100',
            'status' => 'required|in:pending,active,suspended',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Upload the airline logo if a file was provided
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('airlines', 'public');
        }

        // Create a new airline record
        Airline::create($validated);

        // Redirect to the airlines list with a success message
        return redirect()
            ->route('admin.airlines')
            ->with('success', 'Airline created successfully.');
    }

    // Display the form for editing an existing airline
    public function edit($id)
    {
        $airline = Airline::findOrFail($id);

        return view('super-admin.airlines.edit', compact('airline'));
    }

    // Validate and update an existing airline
    public function update(Request $request, $id)
    {
        $airline = Airline::findOrFail($id);

        $request->merge([
            'name' => is_string($request->name) ? trim($request->name) : $request->name,
            'code' => is_string($request->code) ? strtoupper(trim($request->code)) : $request->code,
            'country' => is_string($request->country) ? trim($request->country) : $request->country,
        ]);

        // Validate the submitted airline data
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => [
                'required',
                'string',
                'max:3',
                Rule::unique('airlines', 'code')->ignore($airline->id),
            ],
            'country' => 'required|string|max:100',
            'status' => 'required|in:pending,active,suspended',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Upload the new logo and remove the old one if a file was provided
        if ($request->hasFile('logo')) {
            if ($airline->logo) {
                Storage::disk('public')->delete($airline->logo);
            }

            $validated['logo'] = $request->file('logo')->store('airlines', 'public');
        }

        // Update the airline record
        $airline->update($validated);

        // Redirect to the airlines list with a success message
        return redirect()
            ->route('admin.airlines')
            ->with('success', 'Airline updated successfully.');
    }

    // Delete an airline if it has no associated flights
    public function destroy($id)
    {
        $airline = Airline::findOrFail($id);

        // Prevent deletion if the airline has associated flights
        if ($airline->flights()->exists() || $airline->airlineAdmins()->exists()) {
            return redirect()
                ->route('admin.airlines')
                ->with('error', 'Cannot delete an airline that has associated flights or admins.');
        }

        // Delete the airline logo if it exists
        if ($airline->logo) {
            Storage::disk('public')->delete($airline->logo);
        }

        // Delete the airline record
        $airline->delete();

        // Redirect to the airlines list with a success message
        return redirect()
            ->route('admin.airlines')
            ->with('success', 'Airline deleted successfully.');
    }

    // Suspend an airline without deleting its data
    public function suspend($id)
    {
        $airline = Airline::findOrFail($id);

        // Change the airline status to suspended
        $airline->update([
            'status' => 'suspended',
        ]);

        // Redirect to the airlines list with a success message
        return redirect()
            ->route('admin.airlines')
            ->with('success', 'Airline suspended successfully.');
    }
}
