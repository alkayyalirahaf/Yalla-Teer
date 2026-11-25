<?php

namespace App\Services;

use App\Models\Airport;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FlightSearchService
{
    public function validateFilters(array $input): array
    {
        $airports = null;
        foreach (['from' => 'origin_city_id', 'to' => 'destination_city_id'] as $field => $cityField) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $term = trim($input[$field]);
                $input[$field] = strtoupper($term);
                if ($term === '' || preg_match('/^[a-z]{3}$/i', $term) || mb_strlen($term) > 160) {
                    continue;
                }
                $airports ??= Airport::with('city')->get();
                $key = $this->placeKey($term);
                $matches = $airports->filter(fn ($airport) => $this->matchesPlace($airport->name, $key));
                if ($matches->count() === 1) {
                    $input[$field] = $matches->first()->code;
                } elseif (empty($input[$cityField])) {
                    $cities = $airports->pluck('city')->filter()->unique('id')
                        ->filter(fn ($city) => $this->matchesPlace($city->name, $key));
                    if ($cities->count() === 1) {
                        $input[$cityField] = $cities->first()->id;
                        unset($input[$field]);
                    }
                }
            }
        }

        $trip = $input['trip'] ?? (! empty($input['return']) ? 'round_trip' : 'one_way');
        $input['trip'] = match ($trip) {
            'one-way' => 'one_way',
            'round-trip' => 'round_trip',
            default => $trip,
        };

        $travelers = [];
        if (array_intersect(['adults', 'youth', 'children', 'infants'], array_keys($input))) {
            $travelers = Validator::make($input, [
                'adults' => ['required', 'integer', 'min:1', 'max:9'],
                'youth' => ['sometimes', 'integer', 'min:0', 'max:9'],
                'children' => ['sometimes', 'integer', 'min:0', 'max:9'],
                'infants' => ['sometimes', 'integer', 'min:0', 'max:0'],
                'passengers' => ['sometimes', 'integer', 'min:1', 'max:9'],
            ], ['infants.max' => 'Infant seat rules are not configured yet. Please contact support before searching with an infant.'])->validate();

            $seatsNeeded = (int) $travelers['adults'] + (int) ($travelers['youth'] ?? 0) + (int) ($travelers['children'] ?? 0);
            if (isset($travelers['passengers']) && (int) $travelers['passengers'] !== $seatsNeeded) {
                throw ValidationException::withMessages(['passengers' => 'Seat count must match the submitted traveler counts.']);
            }
            $travelers = [
                'adults' => (int) $travelers['adults'],
                'youth' => (int) ($travelers['youth'] ?? 0),
                'children' => (int) ($travelers['children'] ?? 0),
                'infants' => (int) ($travelers['infants'] ?? 0),
            ];
            $input['passengers'] = $seatsNeeded;
        }

        $input['passengers'] = $input['passengers'] ?? 1;
        $input['sort'] = match ($input['sort'] ?? 'departure_asc') {
            'earliest' => 'departure_asc',
            'latest' => 'departure_desc',
            default => $input['sort'] ?? 'departure_asc',
        };
        $input['leg'] = $input['leg'] ?? 'outbound';
        $input['min_price'] = $input['min_price'] ?? 0;

        $filters = Validator::make($input, [
            'from' => ['nullable', 'required_without:origin_city_id', 'prohibits:origin_city_id', 'string', 'size:3', 'exists:airports,code'],
            'to' => ['nullable', 'required_without:destination_city_id', 'prohibits:destination_city_id', 'string', 'size:3', 'different:from', 'exists:airports,code'],
            'origin_city_id' => ['nullable', 'required_without:from', 'integer', 'exists:cities,id'],
            'destination_city_id' => ['nullable', 'required_without:to', 'integer', 'different:origin_city_id', 'exists:cities,id'],
            'depart' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Amman')->toDateString()],
            'return' => ['nullable', 'required_if:trip,round_trip', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Amman')->toDateString(), 'after_or_equal:depart', 'prohibited_if:trip,one_way'],
            'trip' => ['required', Rule::in(['one_way', 'round_trip'])],
            'cabin' => ['nullable', Rule::in(['economy', 'business', 'first'])],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:min_price'],
            'sort' => ['required', Rule::in(['price_asc', 'price_desc', 'departure_asc', 'departure_desc'])],
            'passengers' => ['required', 'integer', 'min:1', 'max:9'],
            'leg' => ['nullable', Rule::in(['outbound', 'return'])],
            'outbound_class_id' => ['nullable', 'integer', 'exists:flight_classes,id'],
        ])->validate();

        $filters['leg'] = 'outbound';

        return array_merge($filters, $travelers);
    }

    private function placeKey(string $value): string
    {
        return mb_strtolower(strtr(preg_replace('/[\x{064B}-\x{065F}\x{0670}ـ]/u', '', trim($value)), [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ي',
        ]));
    }

    private function matchesPlace(string $name, string $key): bool
    {
        return $this->placeKey($name) === $key || $this->placeKey(__($name, [], 'ar')) === $key;
    }

    public function search(array $filters)
    {
        $filters = $this->validateFilters($filters);
        $sort = $filters['sort'];
        $column = str_starts_with($sort, 'price') ? 'minimum_price' : 'departure_at';
        $direction = str_ends_with($sort, 'desc') ? 'desc' : 'asc';

        return $this->query($filters)->orderBy($column, $direction)->orderBy('id')
            ->paginate(5, ['*'], $filters['leg'] === 'return' ? 'return_page' : 'outbound_page')
            ->withQueryString();
    }

    public function availableFlights(): Builder
    {
        return Flight::whereIn('status', ['scheduled', 'delayed'])
            ->where('departure_at', '>', now())
            ->where('is_return_leg', false)
            ->whereHas('airline', fn ($query) => $query->where('status', 'active'));
    }

    public function classFilter(array $filters): \Closure
    {
        return function ($query) use ($filters) {
            $query->where('price', '>=', $filters['min_price'] ?? 0)
                ->whereHas('seats', fn ($seats) => $seats->availableForBooking(), '>=', (int) ($filters['passengers'] ?? 1));

            if (! empty($filters['cabin'])) {
                $query->where('class', $filters['cabin']);
            }
            if (isset($filters['budget'])) {
                $query->where('price', '<=', $filters['budget']);
            }
        };
    }

    // Call with validated filters. Shared by search and selection revalidation.
    public function query(array $filters): Builder
    {
        $trip = $filters['trip'] ?? 'one_way';
        $originCode = $filters['from'] ?? null;
        $destinationCode = $filters['to'] ?? null;
        $originCity = $filters['origin_city_id'] ?? null;
        $destinationCity = $filters['destination_city_id'] ?? null;
        $classes = $this->classFilter($filters);

        $query = $this->availableFlights()
            ->whereDate('departure_at', $filters['depart'])
            ->whereHas('originAirport', function ($airport) use ($originCode, $originCity) {
                $originCode ? $airport->where('code', $originCode) : $airport->where('city_id', $originCity);
            })
            ->whereHas('destinationAirport', function ($airport) use ($destinationCode, $destinationCity) {
                $destinationCode ? $airport->where('code', $destinationCode) : $airport->where('city_id', $destinationCity);
            })
            ->whereHas('flightClasses', $classes)
            ->with(['airline', 'originAirport.city', 'destinationAirport.city',
                'flightClasses' => function ($query) use ($classes) {
                    $classes($query);
                    $query->withCount(['seats as available_seats_count' => fn ($seats) => $seats->availableForBooking()]);
                }])
            ->withMin(['flightClasses as minimum_price' => $classes], 'price');

        if ($trip === 'round_trip') {
            $query->where('trip_type', 'round_trip')
                ->whereNotNull('return_flight_id')
                ->whereHas('returnFlight', function ($returnFlight) use ($filters, $classes) {
                    $returnFlight->whereIn('status', ['scheduled', 'delayed'])
                        ->whereDate('departure_at', $filters['return'])
                        ->whereColumn('departure_at', '>', 'flights.arrival_at')
                        ->whereHas('airline', fn ($query) => $query->where('status', 'active'))
                        ->whereHas('flightClasses', $classes);
                })
                ->with(['returnFlight.airline', 'returnFlight.originAirport.city', 'returnFlight.destinationAirport.city',
                    'returnFlight.flightClasses' => function ($query) use ($classes) {
                        $classes($query);
                        $query->withCount(['seats as available_seats_count' => fn ($seats) => $seats->availableForBooking()]);
                    }]);
        } else {
            $query->where('trip_type', 'one_way');
        }

        return $query;
    }
}
