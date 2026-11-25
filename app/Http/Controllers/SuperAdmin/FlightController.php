<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FlightController
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['scheduled', 'delayed', 'cancelled', 'completed'])],
            'airline_id' => ['nullable', 'integer', 'exists:airlines,id'],
        ]);

        $flights = Flight::query()
            ->with(['airline', 'originAirport', 'destinationAirport'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where('flight_number', 'like', '%'.$search.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['airline_id'] ?? null, fn ($query, $airlineId) => $query->where('airline_id', $airlineId))
            ->latest('departure_at')
            ->paginate(5)
            ->withQueryString();

        $airlines = Airline::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('super-admin.flights.index', compact('flights', 'airlines', 'filters'));
    }
}
