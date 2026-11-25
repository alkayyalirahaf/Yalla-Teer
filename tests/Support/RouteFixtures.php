<?php

namespace Tests\Support;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\City;
use App\Models\ContactMessage;
use App\Models\Country;
use App\Models\Flight;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RouteFixtures
{
    public static function create(): array
    {
        $admin = User::factory()->create(['name' => 'QA Administrator', 'email' => 'qa.admin@example.test', 'password' => 'RouteQa-2026!']);
        $admin->superAdmin()->create([]);
        $user = User::factory()->create(['name' => 'QA Traveler', 'email' => 'qa.customer@example.test', 'password' => 'RouteQa-2026!']);
        $customer = $user->customer()->create(['credit_balance' => '50.00']);
        $country = Country::create(['name' => 'Jordan', 'code' => 'JO', 'currency' => 'JOD', 'language' => 'Arabic']);
        $city = City::create(['name' => 'Amman', 'country_id' => $country->id]);
        $other = City::create(['name' => 'Aqaba', 'country_id' => $country->id]);
        $airport = Airport::create(['name' => 'Queen Alia International', 'code' => 'AMM', 'city_id' => $city->id]);
        $destination = Airport::create(['name' => 'King Hussein International', 'code' => 'AQJ', 'city_id' => $other->id]);
        $airline = Airline::create(['name' => 'QA Airways', 'code' => 'QA', 'country' => 'Jordan', 'status' => 'active']);
        $airlineUser = User::factory()->create(['name' => 'QA Airline', 'email' => 'qa.airline@example.test', 'password' => 'RouteQa-2026!']);
        $airlineUser->airlineAdmin()->create(['airline_id' => $airline->id]);
        $flights = [];
        foreach ([100, 150, 125, 90] as $index => $price) {
            $past = $index === 3;
            $return = $index === 2;
            $depart = now()->addDays($past ? -5 : ($return ? 7 : 5))->startOfDay()->addHours(12 + $index);
            $flight = Flight::create(['airline_id' => $airline->id, 'flight_number' => 'QA10'.($index + 1),
                'origin_airport_id' => $return ? $destination->id : $airport->id,
                'destination_airport_id' => $return ? $airport->id : $destination->id,
                'departure_at' => $depart, 'arrival_at' => $depart->copy()->addHour(), 'stops' => 0,
                'status' => $past ? 'completed' : 'scheduled']);
            $class = $flight->flightClasses()->create(['class' => 'economy', 'price' => $price, 'seat_rows' => 1,
                'seats_per_row' => 3, 'total_seats' => 3, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 20]);
            foreach (['1A', '1B', '1C'] as $number) {
                Seat::create(['flight_class_id' => $class->id, 'seat_number' => $number, 'status' => 'available']);
            }
            $flights[] = $flight;
        }
        $booking = $customer->bookings()->create(['code' => 'QACOMPLETE', 'trip_type' => 'one_way', 'status' => 'completed',
            'subtotal' => 90, 'taxes' => 0, 'fees' => 0, 'total' => 90, 'credits_used' => 0, 'amount_paid' => 90,
            'contact_email' => $user->email]);
        $booking->payments()->create([
            'stripe_payment_intent_id' => 'pi_route_qa_complete',
            'amount' => 90,
            'status' => 'succeeded',
            'receipt_number' => 'RCP-QA-1',
            'paid_at' => now(),
        ]);
        $segment = $booking->bookingFlights()->create(['flight_id' => $flights[3]->id,
            'flight_class_id' => $flights[3]->flightClasses()->first()->id, 'direction' => 'outbound']);
        $pending = $customer->bookings()->create(['code' => 'QAPENDING', 'trip_type' => 'one_way', 'status' => 'pending',
            'subtotal' => 100, 'taxes' => 0, 'fees' => 0, 'total' => 100, 'credits_used' => 0, 'amount_paid' => 0,
            'contact_email' => $user->email]);
        $file = 'dev3-ai/'.$customer->id.'/'.Str::uuid().'.json';
        Storage::disk('local')->put($file, json_encode(['message' => 'QA saved travel question', 'tips' => ['Verify your itinerary.'],
            'flights' => [], 'recorded_at' => now()->toIso8601String(), 'timezone' => config('app.timezone')]));
        $conversation = $customer->aiConversations()->create(['type' => 'tips', 'title' => 'QA saved conversation', 'file_path' => $file]);

        $reviewSegment = $booking->bookingFlights()->create(['flight_id' => $flights[3]->id,
            'flight_class_id' => $flights[3]->flightClasses()->first()->id, 'direction' => 'outbound']);
        $review = $reviewSegment->reviews()->create(['customer_id' => $customer->id, 'rating' => 5, 'comment' => 'QA review', 'status' => 'hidden']);
        $message = ContactMessage::create(['name' => 'QA', 'email' => 'qa@example.test', 'type' => 'question', 'message' => 'QA message', 'status' => 'new']);

        return compact('admin', 'user', 'airlineUser', 'customer', 'country', 'airport', 'destination', 'booking', 'pending', 'segment', 'conversation', 'flights', 'review', 'message');
    }
}
