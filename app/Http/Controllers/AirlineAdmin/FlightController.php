<?php

namespace App\Http\Controllers\AirlineAdmin;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Services\FlightInventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FlightController
{
    public function __construct(
        protected FlightInventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;

        $flights = Flight::where('airline_id', $airlineId)
            ->with(['originAirport', 'destinationAirport', 'flightClasses'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('flight_number', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest('departure_at')
            ->paginate(5)
            ->withQueryString();

        return view('airline.flights.index', compact('flights'));
    }

    public function create()
    {
        $airports = Airport::with('city')->orderBy('code')->get();
        $airline = auth()->user()->airlineAdmin->airline;
        $layoutPresets = FlightClass::query()
            ->select('class', 'seat_rows', 'seats_per_row', 'cabin_baggage_kg', 'checked_baggage_kg')
            ->distinct()
            ->orderBy('class')
            ->orderByDesc('seat_rows')
            ->get();

        return view('airline.flights.create', compact('airports', 'airline', 'layoutPresets'));
    }

    public function store(Request $request)
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;

        $validated = $request->validate([
            'flight_number' => ['required', 'string', 'max:10'],
            'origin_airport_id' => ['required', 'exists:airports,id'],
            'destination_airport_id' => ['required', 'exists:airports,id', 'different:origin_airport_id'],
            'departure_at' => ['required', 'date', 'after:now'],
            'arrival_at' => ['required', 'date', 'after:departure_at'],
            'stops' => ['required', 'integer', 'min:0', 'max:10'],
            'status' => ['required', Rule::in(['scheduled', 'delayed', 'cancelled', 'completed'])],
            'classes' => ['required', 'array', 'min:1'],
            'classes.*.class' => ['required', 'distinct', Rule::in(['economy', 'business', 'first'])],
            'classes.*.price' => ['required', 'numeric', 'min:1'],
            'classes.*.seat_rows' => ['required', 'integer', 'min:1', 'max:100'],
            'classes.*.seats_per_row' => ['required', 'integer', 'min:1', 'max:10'],
            'classes.*.cabin_baggage_kg' => ['required', 'integer', 'min:0', 'max:100'],
            'classes.*.checked_baggage_kg' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $flight = $this->inventoryService->createFlight($airlineId, $validated, $validated['classes']);

        return redirect()
            ->route('airline.flight', $flight)
            ->with('success', 'Flight and seat inventory created successfully.');
    }

    public function show(Flight $flight)
    {
        $this->authorizeAirlineFlight($flight);

        $flight->load([
            'originAirport.city',
            'destinationAirport.city',
            'flightClasses.seats' => function ($query) {
                $query->orderBy('id');
            },
            'bookingFlights.booking.customer.user',
        ]);

        return view('airline.flights.show', compact('flight'));
    }

    public function edit(Flight $flight)
    {
        $this->authorizeAirlineFlight($flight);

        $flight->load('flightClasses');
        $airports = Airport::with('city')->orderBy('code')->get();
        $airline = auth()->user()->airlineAdmin->airline;
        $layoutPresets = FlightClass::query()
            ->select('class', 'seat_rows', 'seats_per_row', 'cabin_baggage_kg', 'checked_baggage_kg')
            ->distinct()
            ->orderBy('class')
            ->orderByDesc('seat_rows')
            ->get();

        return view('airline.flights.edit', compact('flight', 'airports', 'airline', 'layoutPresets'));
    }

    public function update(Request $request, Flight $flight)
    {
        $this->authorizeAirlineFlight($flight);

        $validated = $request->validate([
            'flight_number' => ['required', 'string', 'max:10'],
            'origin_airport_id' => ['required', 'exists:airports,id'],
            'destination_airport_id' => ['required', 'exists:airports,id', 'different:origin_airport_id'],
            'departure_at' => ['required', 'date', 'after:now'],
            'arrival_at' => ['required', 'date', 'after:departure_at'],
            'stops' => ['required', 'integer', 'min:0', 'max:10'],
            'status' => ['required', Rule::in(['scheduled', 'delayed', 'cancelled', 'completed'])],
            'classes' => ['required', 'array', 'min:1'],
            'classes.*.id' => ['nullable', 'exists:flight_classes,id'],
            'classes.*.class' => ['required', 'distinct', Rule::in(['economy', 'business', 'first'])],
            'classes.*.price' => ['required', 'numeric', 'min:1'],
            'classes.*.seat_rows' => ['required', 'integer', 'min:1', 'max:100'],
            'classes.*.seats_per_row' => ['required', 'integer', 'min:1', 'max:10'],
            'classes.*.cabin_baggage_kg' => ['required', 'integer', 'min:0', 'max:100'],
            'classes.*.checked_baggage_kg' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $this->inventoryService->updateFlight($flight, $validated, $validated['classes']);

        return redirect()
            ->route('airline.flight', $flight)
            ->with('success', 'Flight updated successfully.');
    }

    public function destroy(Flight $flight)
    {
        $this->authorizeAirlineFlight($flight);

        if ($flight->bookingFlights()->exists()) {
            return back()->with('error', 'Cannot delete a flight that already has bookings.');
        }

        $flight->delete();

        return redirect()
            ->route('airline.flights')
            ->with('success', 'Flight deleted successfully.');
    }

    private function authorizeAirlineFlight(Flight $flight): void
    {
        $airlineId = auth()->user()->airlineAdmin->airline_id;

        if ($flight->airline_id !== $airlineId) {
            abort(403, 'Unauthorized access to this flight.');
        }
    }
}
