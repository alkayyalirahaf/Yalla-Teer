<?php

namespace Database\Seeders;

use App\Models\AiConversation;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\BookingFlight;
use App\Models\City;
use App\Models\ContactMessage;
use App\Models\Country;
use App\Models\CreditTransaction;
use App\Models\Flight;
use App\Models\NotificationLog;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Recommendation;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\User;
use App\Services\BookingFinalizationService;
use App\Services\FlightInventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Fictional, repeatable fixtures. Never sends mail or calls payment/AI providers. */
class DemoSeeder extends Seeder
{
    public const PASSWORD = 'FlyNow-Demo-2026!';

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('DemoSeeder is restricted to local and testing environments.');
        }

        DB::transaction(function (): void {
            $airports = $this->destinations();
            $airlines = [];
            foreach ([['RJ', 'Royal Jordanian', 'Jordan', 'active'], ['EK', 'Emirates', 'United Arab Emirates', 'active'], ['TK', 'Turkish Airlines', 'Turkey', 'active'], ['DMO', 'Demo Pending Airways', 'Jordan', 'pending'], ['DMS', 'Demo Suspended Airways', 'Jordan', 'suspended']] as [$code, $name, $country, $status]) {
                $airlines[$code] = Airline::firstOrCreate(['code' => $code], compact('name', 'country', 'status'));
            }
            $admin = $this->user('admin', 'Demo Platform Admin');
            $admin->superAdmin()->firstOrCreate([]);
            foreach (['RJ', 'EK', 'TK'] as $code) {
                $this->user('staff.'.strtolower($code), $code.' Demo Staff')->airlineAdmin()->firstOrCreate([], ['airline_id' => $airlines[$code]->id, 'job_title' => 'Flight Operations']);
            }
            $customers = [];
            foreach (['traveler' => 'Sara Demo', 'family' => 'Omar Demo', 'business' => 'Lina Demo', 'new' => 'Adam Demo'] as $key => $name) {
                $customers[] = $this->user($key, $name)->customer()->firstOrCreate([], ['credit_balance' => 0]);
            }
            $flights = $this->flights($airports, $airlines);
            $this->dubaiComparisonFlights($airports, $airlines);
            $this->bookings($customers, $flights);
            foreach ($customers as $index => $customer) {
                foreach (['find', 'compare', 'tips'] as $mode) {
                    $path = 'demo-ai/'.$customer->id.'-'.$mode.'.json';
                    if (! Storage::disk('local')->exists($path)) {
                        Storage::disk('local')->put($path, json_encode(['message' => 'Demo: help plan a trip to Dubai.', 'tips' => 'Fictional saved conversation for UI testing. Compare baggage and departure times before choosing.', 'flights' => [], 'recorded_at' => now()->toIso8601String(), 'timezone' => config('app.timezone')], JSON_THROW_ON_ERROR));
                    }
                    $conversation = AiConversation::firstOrCreate(['file_path' => $path], ['customer_id' => $customer->id, 'type' => $mode, 'title' => 'Demo '.$mode.': planning a Dubai trip']);
                    Recommendation::firstOrCreate(['customer_id' => $customer->id, 'flight_id' => $flights[3][0][0]['outbound']->id, 'ai_conversation_id' => $conversation->id], ['reason' => 'Demo recommendation: convenient departure and economy fare.', 'source' => 'chatbot']);
                }
                $note = 'Demo opening balance '.$customer->id;
                if (! CreditTransaction::where('customer_id', $customer->id)->where('note', $note)->exists()) {
                    $balance = (float) $customer->credit_balance + 100 + $index * 50;
                    CreditTransaction::create(['customer_id' => $customer->id, 'type' => 'admin_adjust', 'amount' => 100 + $index * 50, 'balance_after' => $balance, 'note' => $note, 'actor_user_id' => $admin->id]);
                    $customer->update(['credit_balance' => $balance]);
                }
                foreach (['question', 'suggestion', 'complaint'] as $type) {
                    ContactMessage::firstOrCreate(['email' => $customer->user->email, 'message' => 'Demo '.$type.': sample message for the support dashboard.'], ['customer_id' => $customer->id, 'name' => $customer->user->name, 'type' => $type, 'status' => $type === 'question' ? 'handled' : 'new']);
                }
            }
        });
    }

    private function user(string $key, string $name): User
    {
        return User::firstOrCreate(['email' => $key.'@demo.example.test'], ['name' => $name, 'password' => self::PASSWORD, 'email_verified_at' => now()]);
    }

    private function destinations(): array
    {
        $airports = [];
        $rows = [
            ['JO', 'Jordan', 'JOD', 'Arabic', 'Amman', 'AMM', 'Queen Alia International Airport', 'Spring and autumn'],
            ['AE', 'United Arab Emirates', 'AED', 'Arabic', 'Dubai', 'DXB', 'Dubai International Airport', 'November to March'],
            ['EG', 'Egypt', 'EGP', 'Arabic', 'Cairo', 'CAI', 'Cairo International Airport', 'October to April'],
            ['TR', 'Turkey', 'TRY', 'Turkish', 'Istanbul', 'IST', 'Istanbul Airport', 'Spring and autumn'],
            ['SA', 'Saudi Arabia', 'SAR', 'Arabic', 'Riyadh', 'RUH', 'King Khalid International Airport', 'November to February'],
            ['QA', 'Qatar', 'QAR', 'Arabic', 'Doha', 'DOH', 'Hamad International Airport', 'November to March'],
            ['GB', 'United Kingdom', 'GBP', 'English', 'London', 'LHR', 'Heathrow Airport', 'May to September'],
            ['FR', 'France', 'EUR', 'French', 'Paris', 'CDG', 'Charles de Gaulle Airport', 'Spring and autumn'],
            ['IT', 'Italy', 'EUR', 'Italian', 'Rome', 'FCO', 'Leonardo da Vinci Airport', 'Spring and autumn'],
            ['TH', 'Thailand', 'THB', 'Thai', 'Bangkok', 'BKK', 'Suvarnabhumi Airport', 'November to February'],
            ['JP', 'Japan', 'JPY', 'Japanese', 'Tokyo', 'HND', 'Haneda Airport', 'Spring and autumn'],
            ['US', 'United States', 'USD', 'English', 'New York', 'JFK', 'John F. Kennedy International Airport', 'Spring and autumn'],
        ];
        foreach ($rows as [$code, $name, $currency, $language, $cityName, $airportCode, $airportName, $season]) {
            $country = Country::firstOrCreate(['code' => $code], compact('name', 'currency', 'language'));
            if ($country->local_apps === null) {
                $country->update(['local_apps' => 'Demo travel guide: offline maps and airline app.']);
            }
            $city = City::firstOrCreate(['country_id' => $country->id, 'name' => $cityName]);
            $city->fill(array_filter(['airport_transport' => $city->airport_transport === null ? 'Use licensed airport taxis or published public transport services.' : null, 'tips' => $city->tips === null ? 'Sample destination guide: allow time for airport transfers and check local opening hours.' : null, 'best_season' => $city->best_season === null ? $season : null]))->save();
            $airports[$airportCode] = Airport::firstOrCreate(['code' => $airportCode], ['city_id' => $city->id, 'name' => $airportName]);
        }

        return $airports;
    }

    private function flights(array $airports, array $airlines): array
    {
        $flights = [];
        $inventory = app(FlightInventoryService::class);
        // Three carriers on the same route/date enable comparison; round-trip products pair outbound and return legs.
        foreach ([1, 3, 7, 14, 21, 30, -7] as $day) {
            foreach (['DXB', 'CAI', 'IST', 'RUH', 'DOH', 'LHR', 'CDG', 'FCO', 'BKK', 'HND', 'JFK'] as $route => $destination) {
                foreach (['RJ', 'EK', 'TK'] as $carrier => $code) {
                    $number = $code.'D'.sprintf('%02d', $route).$carrier.'O';
                    $departure = today()->addDays($day)->setTime(8 + $carrier * 3, 0);
                    $flight = Flight::where('flight_number', $number)->where('departure_at', $departure)->first();
                    if (! $flight) {
                        $base = 90 + $route * 45 + $carrier * 25;
                        $returnDeparture = today()->addDays($day + 4)->setTime(8 + $carrier * 3, 0);
                        $status = $day < 0 ? 'completed' : ($day === 21 && $carrier === 2 ? 'cancelled' : ($day === 14 && $carrier === 1 ? 'delayed' : 'scheduled'));
                        $flight = $inventory->createFlight($airlines[$code]->id, [
                            'trip_type' => 'round_trip',
                            'flight_number' => $number,
                            'origin_airport_id' => $airports['AMM']->id,
                            'destination_airport_id' => $airports[$destination]->id,
                            'departure_at' => $departure->toDateTimeString(),
                            'arrival_at' => $departure->copy()->addHours(2 + $route)->toDateTimeString(),
                            'stops' => $route > 7 ? 1 : 0,
                            'status' => $status,
                            'return_flight_number' => $code.'D'.sprintf('%02d', $route).$carrier.'R',
                            'return_departure_at' => $returnDeparture->toDateTimeString(),
                            'return_arrival_at' => $returnDeparture->copy()->addHours(2 + $route)->toDateTimeString(),
                            'return_stops' => $route > 7 ? 1 : 0,
                        ], [
                            ['class' => 'economy', 'price' => $base, 'seat_rows' => 10, 'seats_per_row' => 6, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 23],
                            ['class' => 'business', 'price' => $base * 2.5, 'seat_rows' => 3, 'seats_per_row' => 4, 'cabin_baggage_kg' => 10, 'checked_baggage_kg' => 32],
                            ['class' => 'first', 'price' => $base * 4, 'seat_rows' => 2, 'seats_per_row' => 2, 'cabin_baggage_kg' => 14, 'checked_baggage_kg' => 40],
                        ]);
                    }
                    $flight->loadMissing('returnFlight');
                    $flights[$day][$route][$carrier]['outbound'] = $flight;
                    $flights[$day][$route][$carrier]['return'] = $flight->returnFlight;
                }
            }
        }

        return $flights;
    }

    private function dubaiComparisonFlights(array $airports, array $airlines): void
    {
        $inventory = app(FlightInventoryService::class);
        $offers = [
            [1, 'AMM', 'DXB', 'TK', 'TKQA101', 9, 82],
            [1, 'AMM', 'DXB', 'EK', 'EKQA102', 19, 155],
            [10, 'DXB', 'AMM', 'RJ', 'RJQA201', 8, 112],
            [10, 'DXB', 'AMM', 'EK', 'EKQA202', 13, 98],
            [10, 'DXB', 'AMM', 'TK', 'TKQA203', 18, 85],
            [10, 'DXB', 'AMM', 'RJ', 'RJQA204', 21, 145],
        ];

        foreach ($offers as [$day, $origin, $destination, $carrier, $number, $hour, $fare]) {
            $departure = today()->addDays($day)->setTime($hour, 0);
            if (Flight::where('flight_number', $number)->where('departure_at', $departure)->exists()) {
                continue;
            }

            $inventory->createFlight($airlines[$carrier]->id, [
                'flight_number' => $number,
                'origin_airport_id' => $airports[$origin]->id,
                'destination_airport_id' => $airports[$destination]->id,
                'departure_at' => $departure->toDateTimeString(),
                'arrival_at' => $departure->copy()->addHours(3)->toDateTimeString(),
                'stops' => 0,
                'status' => 'scheduled',
            ], [
                ['class' => 'economy', 'price' => $fare, 'seat_rows' => 10, 'seats_per_row' => 6, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 23],
            ]);
        }
    }

    private function bookings(array $customers, array $flights): void
    {
        $scenarios = [
            ['confirmed', 7],
            ['completed', -7],
            ['cancelled', 7],
            ['pending', 7],
            ['confirmed', 1],
            ['confirmed', 1, 'dubai_round_trip'],
        ];

        foreach ($scenarios as $index => $scenario) {
            [$status, $day] = $scenario;
            $dubaiRoundTrip = ($scenario[2] ?? null) === 'dubai_round_trip';
            $scenarioCustomers = $dubaiRoundTrip ? array_slice($customers, 0, 1) : $customers;
            foreach ($scenarioCustomers as $customerIndex => $customer) {
                $code = 'DEMO'.sprintf('%02d%02d', $index, $customerIndex);
                if (Booking::where('code', $code)->exists()) {
                    continue;
                }
                $round = $customerIndex === 1 || $dubaiRoundTrip;
                $carrier = $day === 1 ? $customerIndex % 3 : $index % 3;
                $legs = $flights[$day][$customerIndex][$carrier];
                if ($dubaiRoundTrip) {
                    $legs['return'] = Flight::where('airline_id', $legs['outbound']->airline_id)
                        ->where('flight_number', $legs['return']->flight_number)
                        ->where('origin_airport_id', $legs['outbound']->destination_airport_id)
                        ->where('destination_airport_id', $legs['outbound']->origin_airport_id)
                        ->where('departure_at', '>=', today()->addDays(3))
                        ->where('departure_at', '<=', $legs['return']->departure_at)
                        ->where('status', 'scheduled')
                        ->whereHas('flightClasses', fn ($query) => $query->where('class', 'economy'))
                        ->orderBy('departure_at')
                        ->firstOrFail();
                }
                if (! $round) {
                    unset($legs['return']);
                }
                $count = $round ? 3 : ($day === 1 ? 2 : 1);
                $classes = collect($legs)->map(fn ($flight) => $flight->flightClasses()->where('class', $customerIndex === 2 ? 'business' : 'economy')->firstOrFail());
                $total = $classes->sum('price') * $count;
                $paid = in_array($status, ['confirmed', 'completed'], true);
                $booking = Booking::create(['code' => $code, 'customer_id' => $customer->id, 'trip_type' => $round ? 'round_trip' : 'one_way', 'status' => $status, 'subtotal' => $total, 'taxes' => 0, 'fees' => 0, 'total' => $total, 'credits_used' => 0, 'amount_paid' => $paid ? $total : 0, 'contact_email' => $customer->user->email]);
                $passengers = [];
                for ($p = 0; $p < $count; $p++) {
                    $passengers[] = Passenger::create(['booking_id' => $booking->id, 'type' => $p === 2 ? 'child' : 'adult', 'first_name' => ['Sara', 'Omar', 'Adam'][$p], 'last_name' => 'Demo', 'date_of_birth' => $p === 2 ? today()->subYears(8)->toDateString() : '1992-06-15', 'gender' => $p === 0 ? 'female' : 'male', 'nationality' => 'Jordanian', 'passport_number' => 'TEST'.$code.$p, 'passport_country' => 'Jordan', 'passport_expiry' => today()->addYears(5), 'filled_by_ai' => false]);
                }
                $held = [];
                $expires = now()->addMinutes(15)->startOfSecond();
                foreach ($legs as $direction => $flight) {
                    $class = $classes[$direction];
                    $leg = BookingFlight::create(['booking_id' => $booking->id, 'flight_id' => $flight->id, 'flight_class_id' => $class->id, 'direction' => $direction]);
                    if ($paid) {
                        foreach ($passengers as $p => $passenger) {
                            $seat = $class->seats()->where('status', 'available')->orderBy('id')->firstOrFail();
                            $seat->update(['status' => 'booked']);
                            $number = $code.($direction === 'return' ? 'R' : 'O').$p;
                            Ticket::create(['booking_flight_id' => $leg->id, 'passenger_id' => $passenger->id, 'seat_id' => $seat->id, 'ticket_number' => $number, 'qr_code' => $number, 'status' => $status === 'completed' ? 'used' : 'issued']);
                        }
                    }
                    if ($status === 'completed') {
                        Review::create(['booking_flight_id' => $leg->id, 'customer_id' => $customer->id, 'rating' => 3 + $customerIndex % 3, 'comment' => 'Demo review: helpful crew and a comfortable journey.', 'status' => $customerIndex === 3 ? 'hidden' : 'visible']);
                    }
                    if ($status === 'pending') {
                        foreach ($passengers as $passenger) {
                            $seat = $class->seats()->where('status', 'available')->orderBy('id')->firstOrFail();
                            $seat->update(['status' => 'held', 'held_by' => $customer->id, 'held_until' => $expires]);
                            $held[$direction][] = $seat->id;
                        }
                    }
                }
                if ($held) {
                    Cache::store('database')->put(BookingFinalizationService::holdKey($booking), ['seats' => $held, 'expires' => $expires->timestamp], $expires);
                }
                Payment::create(['booking_id' => $booking->id, 'amount' => $total, 'status' => $paid ? 'succeeded' : ($status === 'cancelled' ? 'failed' : 'pending'), 'receipt_number' => $paid ? 'DEMO-'.$code : null, 'paid_at' => $paid ? now() : null]);
                if ($paid) {
                    // Historical log only: no queued/scheduled notifications can be sent by this seeder.
                    NotificationLog::create(['booking_id' => $booking->id, 'channel' => 'email', 'type' => 'confirmation', 'scheduled_at' => now(), 'sent_at' => now(), 'status' => 'sent']);
                }
            }
        }
    }
}
