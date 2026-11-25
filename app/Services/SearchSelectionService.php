<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SearchSelectionService
{
    public function select(array $input, FlightSearchService $search): array
    {
        $filters = $search->validateFilters(array_merge($input, ['leg' => 'outbound']));
        if (! isset($filters['adults'])) {
            throw ValidationException::withMessages([
                'adults' => 'Choose the adult, youth and child counts and search again before selecting flights.',
            ]);
        }

        $passengerCounts = [
            'adults' => $filters['adults'],
            'youth' => $filters['youth'],
            'children' => $filters['children'],
            'infants' => $filters['infants'],
        ];
        $ids = Validator::make($input, [
            'outbound_class_id' => ['required', 'integer', 'min:1'],
            'return_class_id' => ['prohibited'],
        ])->validate();

        $outboundFlight = $search->query($filters)->whereHas('flightClasses', function ($query) use ($ids, $search, $filters) {
            $query->whereKey($ids['outbound_class_id']);
            ($search->classFilter($filters))($query);
        })->first();

        if (! $outboundFlight) {
            throw ValidationException::withMessages(['selection' => 'A selected cabin no longer matches the search or has insufficient available seats. Search again.']);
        }

        $outboundClass = $outboundFlight->flightClasses->firstWhere('id', (int) $ids['outbound_class_id']);
        $outbound = ['flight_id' => $outboundFlight->id, 'flight_class_id' => $outboundClass->id, 'price' => $outboundClass->price];
        $return = null;

        if ($filters['trip'] === 'round_trip') {
            $returnFlight = $outboundFlight->returnFlight;
            $returnClass = $returnFlight?->flightClasses?->firstWhere('class', $outboundClass->class);

            if (! $returnFlight || ! $returnClass) {
                throw ValidationException::withMessages(['selection' => 'The selected round-trip flight is no longer available. Search again.']);
            }

            $return = ['flight_id' => $returnFlight->id, 'flight_class_id' => $returnClass->id, 'price' => $returnClass->price];
        }

        return [
            'trip' => $filters['trip'], 'passengers' => (int) $filters['passengers'],
            'passenger_counts' => $passengerCounts,
            'filters' => $filters, 'outbound' => $outbound, 'return' => $return,
        ];
    }

    private function leg(array $filters, int $classId, FlightSearchService $search): array
    {
        $flight = $search->query($filters)->whereHas('flightClasses', function ($query) use ($classId, $search, $filters) {
            $query->whereKey($classId);
            ($search->classFilter($filters))($query);
        })->first();

        if (! $flight) {
            throw ValidationException::withMessages(['selection' => 'A selected cabin no longer matches the search or has insufficient available seats. Search again.']);
        }

        $class = $flight->flightClasses->firstWhere('id', $classId);

        return ['flight_id' => $flight->id, 'flight_class_id' => $class->id, 'price' => $class->price];
    }
}
