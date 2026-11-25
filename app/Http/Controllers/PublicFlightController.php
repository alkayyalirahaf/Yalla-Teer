<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\City;
use App\Models\Flight;
use App\Services\FlightSearchService;
use App\Services\SearchSelectionService;
use Illuminate\Http\Request;

class PublicFlightController
{
    public function index(Request $request, FlightSearchService $search)
    {
        $filters = [];
        $flights = null;
        if ($request->hasAny(['from', 'to', 'origin_city_id', 'destination_city_id', 'depart', 'trip', 'legs'])) {
            $filters = $search->validateFilters($request->query());
            $flights = $search->search($filters);
            $request->session()->put('dev3.search_filters', $filters);
        }

        $airports = Airport::with('city')->orderBy('code')->get();
        $cities = City::orderBy('name')->get();

        return view('flights.index', compact('filters', 'flights', 'airports', 'cities'));
    }

    public function show(int $flight, FlightSearchService $search)
    {
        $flight = $search->availableFlights()->with([
            'airline', 'originAirport.city', 'destinationAirport.city',
            'returnFlight.airline', 'returnFlight.originAirport.city', 'returnFlight.destinationAirport.city',
            'returnFlight.flightClasses' => fn ($query) => $query->withCount([
                'seats as available_seats_count' => fn ($seats) => $seats->availableForBooking(),
            ])->orderBy('price'),
            'flightClasses' => fn ($query) => $query->withCount([
                'seats as available_seats_count' => fn ($seats) => $seats->availableForBooking(),
            ])->orderBy('price'),
        ])->findOrFail($flight);

        return view('flights.show', compact('flight'));
    }

    public function select(Request $request, SearchSelectionService $selection, FlightSearchService $search)
    {
        $selection = $selection->select($request->all(), $search);
        $request->session()->put('dev3.search_selection', $selection);

        $loadSelectedFlights = fn () => Flight::with('airline')->whereKey(array_filter([
            $selection['outbound']['flight_id'],
            $selection['return']['flight_id'] ?? null,
        ]))->get()->keyBy('id');

        return view('flights.selection', compact('selection', 'loadSelectedFlights'));
    }
}
