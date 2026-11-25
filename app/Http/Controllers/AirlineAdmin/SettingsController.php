<?php

namespace App\Http\Controllers\AirlineAdmin;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController
{
    public function edit()
    {
        $airline = auth()->user()->airlineAdmin?->airline;
        abort_unless($airline, 403, 'No airline is assigned to this account.');

        $countries = Country::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('airline.settings', compact('airline', 'countries'));
    }

    public function update(Request $request)
    {
        $airline = auth()->user()->airlineAdmin?->airline;
        abort_unless($airline, 403, 'No airline is assigned to this account.');

        $validated = $request->validate([
            'country' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['country'] = trim($validated['country']);

        if ($request->hasFile('logo')) {
            if ($airline->logo) {
                Storage::disk('public')->delete($airline->logo);
            }

            $validated['logo'] = $request->file('logo')->store('airlines', 'public');
        }

        $airline->update($validated);

        return redirect()
            ->route('airline.settings')
            ->with('success', 'Airline settings updated successfully.');
    }
}
