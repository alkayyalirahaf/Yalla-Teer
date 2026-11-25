<?php

namespace App\Services;

use App\Models\Flight;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FlightInventoryService
{
    public function createFlight(int $airlineId, array $flightData, array $classesData): Flight
    {
        $tripType = $flightData['trip_type'] ?? 'one_way';
        $this->validateFlightRules($flightData, $classesData, $tripType);

        return DB::transaction(function () use ($airlineId, $flightData, $classesData, $tripType) {
            $flight = Flight::create([
                'airline_id' => $airlineId,
                'flight_number' => $flightData['flight_number'],
                'origin_airport_id' => $flightData['origin_airport_id'],
                'destination_airport_id' => $flightData['destination_airport_id'],
                'departure_at' => $flightData['departure_at'],
                'arrival_at' => $flightData['arrival_at'],
                'stops' => $flightData['stops'] ?? 0,
                'status' => $flightData['status'] ?? 'scheduled',
                'trip_type' => $tripType,
                'is_return_leg' => false,
            ]);

            $this->syncClassesAndSeats($flight, $classesData);

            if ($tripType === 'round_trip') {
                $returnFlight = Flight::create([
                    'airline_id' => $airlineId,
                    'flight_number' => $flightData['return_flight_number'],
                    'origin_airport_id' => $flightData['destination_airport_id'],
                    'destination_airport_id' => $flightData['origin_airport_id'],
                    'departure_at' => $flightData['return_departure_at'],
                    'arrival_at' => $flightData['return_arrival_at'],
                    'stops' => $flightData['return_stops'] ?? 0,
                    'status' => $flightData['status'] ?? 'scheduled',
                    'trip_type' => 'round_trip',
                    'is_return_leg' => true,
                ]);

                $this->syncClassesAndSeats($returnFlight, $classesData);
                $flight->update(['return_flight_id' => $returnFlight->id]);
            }

            return $flight->load(['flightClasses.seats', 'originAirport', 'destinationAirport', 'returnFlight.flightClasses.seats', 'returnFlight.originAirport', 'returnFlight.destinationAirport']);
        });
    }

    public function updateFlight(Flight $flight, array $flightData, array $classesData): Flight
    {
        $tripType = $flightData['trip_type'] ?? $flight->trip_type ?? 'one_way';
        $this->validateFlightRules($flightData, $classesData, $tripType, $flight);

        return DB::transaction(function () use ($flight, $flightData, $classesData, $tripType) {
            $flight->update([
                'flight_number' => $flightData['flight_number'],
                'origin_airport_id' => $flightData['origin_airport_id'],
                'destination_airport_id' => $flightData['destination_airport_id'],
                'departure_at' => $flightData['departure_at'],
                'arrival_at' => $flightData['arrival_at'],
                'stops' => $flightData['stops'] ?? 0,
                'status' => $flightData['status'] ?? $flight->status,
                'trip_type' => $tripType,
            ]);

            $this->syncClassesAndSeats($flight, $classesData);

            if ($tripType === 'round_trip') {
                $returnFlight = $flight->returnFlight;
                $returnData = [
                    'airline_id' => $flight->airline_id,
                    'flight_number' => $flightData['return_flight_number'],
                    'origin_airport_id' => $flightData['destination_airport_id'],
                    'destination_airport_id' => $flightData['origin_airport_id'],
                    'departure_at' => $flightData['return_departure_at'],
                    'arrival_at' => $flightData['return_arrival_at'],
                    'stops' => $flightData['return_stops'] ?? 0,
                    'status' => $flightData['status'] ?? $flight->status,
                    'trip_type' => 'round_trip',
                    'is_return_leg' => true,
                ];

                if ($returnFlight) {
                    $returnFlight->update($returnData);
                } else {
                    $returnFlight = Flight::create($returnData);
                    $flight->update(['return_flight_id' => $returnFlight->id]);
                }

                $this->syncClassesAndSeats($returnFlight, $classesData);
            } elseif ($flight->return_flight_id) {
                $returnFlight = $flight->returnFlight;
                $flight->update(['return_flight_id' => null]);
                if ($returnFlight && ! $returnFlight->bookingFlights()->exists()) {
                    $returnFlight->delete();
                } elseif ($returnFlight) {
                    $returnFlight->update(['is_return_leg' => false, 'trip_type' => 'one_way']);
                }
            }

            return $flight->fresh(['flightClasses.seats', 'originAirport', 'destinationAirport', 'returnFlight.flightClasses.seats', 'returnFlight.originAirport', 'returnFlight.destinationAirport']);
        });
    }

    protected function validateFlightRules(array $flightData, array $classesData, string $tripType = 'one_way', ?Flight $flight = null): void
    {
        if ((int) $flightData['origin_airport_id'] === (int) $flightData['destination_airport_id']) {
            throw ValidationException::withMessages([
                'destination_airport_id' => 'Destination airport must be different from origin airport.',
            ]);
        }

        if (strtotime($flightData['arrival_at']) <= strtotime($flightData['departure_at'])) {
            throw ValidationException::withMessages([
                'arrival_at' => 'Arrival time must be after departure time.',
            ]);
        }

        if ($tripType === 'round_trip') {
            foreach (['return_flight_number', 'return_departure_at', 'return_arrival_at'] as $field) {
                if (blank($flightData[$field] ?? null)) {
                    throw ValidationException::withMessages([$field => 'The return flight field is required for round-trip flights.']);
                }
            }

            if (strtotime($flightData['return_arrival_at']) <= strtotime($flightData['return_departure_at'])) {
                throw ValidationException::withMessages([
                    'return_arrival_at' => 'Return arrival time must be after return departure time.',
                ]);
            }

            if (strtotime($flightData['return_departure_at']) < strtotime($flightData['arrival_at'])) {
                throw ValidationException::withMessages([
                    'return_departure_at' => 'Return departure must be after the outbound arrival time.',
                ]);
            }
        }

        $classTypes = array_column($classesData, 'class');
        if (count($classTypes) !== count(array_unique($classTypes))) {
            throw ValidationException::withMessages([
                'classes' => 'Duplicate cabin classes are not allowed on the same flight.',
            ]);
        }
    }

    protected function syncClassesAndSeats(Flight $flight, array $classesData): void
    {
        $keptClassIds = [];

        foreach ($classesData as $classItem) {
            $rows = (int) $classItem['seat_rows'];
            $perRow = (int) $classItem['seats_per_row'];
            $totalSeats = $rows * $perRow;

            $flightClass = isset($classItem['id'])
                ? $flight->flightClasses()->where('id', $classItem['id'])->firstOrFail()
                : $flight->flightClasses()->firstOrNew(['class' => $classItem['class']]);

            if ($flightClass->exists) {
                $protectedCount = $flightClass->seats()->whereIn('status', ['booked', 'held'])->count();
                if ($totalSeats < $protectedCount) {
                    throw ValidationException::withMessages([
                        'classes' => "Cannot reduce seats in {$flightClass->class} below active/booked seats ({$protectedCount}).",
                    ]);
                }
            }

            $flightClass->fill([
                'class' => $classItem['class'],
                'price' => $classItem['price'],
                'seat_rows' => $rows,
                'seats_per_row' => $perRow,
                'total_seats' => $totalSeats,
                'cabin_baggage_kg' => $classItem['cabin_baggage_kg'] ?? 7,
                'checked_baggage_kg' => $classItem['checked_baggage_kg'] ?? 23,
            ]);
            $flightClass->save();

            $keptClassIds[] = $flightClass->id;

            $desiredSeatNumbers = [];
            $letters = range('A', 'Z');

            for ($r = 1; $r <= $rows; $r++) {
                for ($c = 0; $c < $perRow; $c++) {
                    $desiredSeatNumbers[] = $r.$letters[$c];
                }
            }

            $conflictingSeat = $flightClass->seats()
                ->whereIn('status', ['booked', 'held'])
                ->whereNotIn('seat_number', $desiredSeatNumbers)
                ->exists();

            if ($conflictingSeat) {
                throw ValidationException::withMessages([
                    'classes' => "Cannot change seat layout for {$flightClass->class} because booked/held seats would be removed.",
                ]);
            }

            $flightClass->seats()
                ->where('status', 'available')
                ->whereNotIn('seat_number', $desiredSeatNumbers)
                ->delete();

            $existingNumbers = $flightClass->seats()->pluck('seat_number')->all();
            $newSeats = [];
            $now = now();

            foreach ($desiredSeatNumbers as $seatNumber) {
                if (! in_array($seatNumber, $existingNumbers, true)) {
                    $newSeats[] = [
                        'flight_class_id' => $flightClass->id,
                        'seat_number' => $seatNumber,
                        'status' => 'available',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! empty($newSeats)) {
                Seat::insert($newSeats);
            }
        }

        $classesToDelete = $flight->flightClasses()->whereNotIn('id', $keptClassIds)->get();
        foreach ($classesToDelete as $oldClass) {
            if ($oldClass->seats()->whereIn('status', ['booked', 'held'])->exists()) {
                throw ValidationException::withMessages([
                    'classes' => "Cannot remove {$oldClass->class} class because it has booked or held seats.",
                ]);
            }
            $oldClass->seats()->delete();
            $oldClass->delete();
        }
    }
}
