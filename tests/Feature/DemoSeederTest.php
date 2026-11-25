<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_is_complete_consistent_and_repeatable_without_overwriting_edits(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->seed(DemoSeeder::class);

        $tables = ['countries', 'cities', 'airports', 'airlines', 'users', 'super_admins', 'airline_admins', 'customers', 'flights', 'flight_classes', 'seats', 'bookings', 'booking_flights', 'passengers', 'tickets', 'payments', 'credit_transactions', 'reviews', 'notification_logs', 'contact_messages', 'ai_conversations', 'recommendations'];
        $counts = [];
        foreach ($tables as $table) {
            $counts[$table] = DB::table($table)->count();
            $this->assertGreaterThan(0, $counts[$table], $table);
        }
        $this->assertSame(468, Flight::count());
        $this->assertSame(21, Booking::count());
        $dubaiEconomy = Flight::where('flight_number', 'RJD000O')
            ->whereDate('departure_at', today()->addDay()->toDateString())
            ->firstOrFail()->flightClasses()->where('class', 'economy')->firstOrFail();
        $this->assertSame(['1A', '1B', '1C', '1D', '1E'], $dubaiEconomy->seats()->where('status', 'booked')->orderBy('seat_number')->pluck('seat_number')->all());
        $this->assertSame(55, $dubaiEconomy->seats()->where('status', 'available')->count());
        $user = User::where('email', 'traveler@demo.example.test')->firstOrFail();
        $this->assertTrue(Hash::check(DemoSeeder::PASSWORD, $user->password));
        $user->update(['name' => 'Edited traveler']);
        $this->seed(DemoSeeder::class);
        foreach ($tables as $table) {
            $this->assertSame($counts[$table], DB::table($table)->count(), $table);
        }
        $this->assertSame('Edited traveler', $user->fresh()->name);
        foreach (Ticket::with(['seat', 'bookingFlight', 'passenger'])->get() as $ticket) {
            $this->assertSame('booked', $ticket->seat->status);
            $this->assertSame($ticket->bookingFlight->flight_class_id, $ticket->seat->flight_class_id);
            $this->assertSame($ticket->bookingFlight->booking_id, $ticket->passenger->booking_id);
        }
        $this->assertSame(Ticket::count(), Ticket::distinct()->count('seat_id'));
        $this->assertGreaterThan(0, Seat::where('status', 'held')->count());
        foreach (Booking::whereIn('status', ['confirmed', 'completed'])->get() as $booking) {
            $this->assertSame($booking->total, $booking->amount_paid);
        }
        Mail::assertNothingSent();
        $this->actingAs($user)->get('/my/bookings')->assertOk()->assertSee('DEMO');
        $this->get('/my/credits')->assertOk();
        $this->get('/assistant')->assertOk()->assertSee('planning a Dubai trip');
        $this->get('/flights?from=AMM&to=DXB&depart='.today()->addDays(3)->toDateString().'&trip=one_way')->assertOk()->assertSee('Royal Jordanian')->assertSee('Emirates');
        $search = [
            'from' => 'AMM', 'to' => 'DXB', 'depart' => today()->addDay()->toDateString(),
            'return' => today()->addDays(10)->toDateString(), 'trip' => 'round_trip',
            'adults' => 1, 'cabin' => 'economy', 'min_price' => 0, 'budget' => 115,
        ];
        $outbound = [
            'departure_asc' => ['RJD000O', 'TKQA101', 'EKD001O'],
            'departure_desc' => ['EKD001O', 'TKQA101', 'RJD000O'],
            'price_asc' => ['TKQA101', 'RJD000O', 'EKD001O'],
            'price_desc' => ['EKD001O', 'RJD000O', 'TKQA101'],
        ];
        foreach ($outbound as $sort => $numbers) {
            $this->get(route('flights.search', $search + ['sort' => $sort]))
                ->assertOk()->assertSeeInOrder($numbers)->assertDontSee('EKQA102')->assertDontSee('TKD002O');
        }
        $offers = Flight::whereIn('flight_number', $outbound['departure_asc'])->whereDate('departure_at', $search['depart'])->get();
        $selected = $offers->firstWhere('flight_number', 'TKQA101')->flightClasses()->firstOrFail();
        $this->get(route('flights.search', $search + ['leg' => 'return', 'outbound_class_id' => $selected->id, 'sort' => 'departure_desc']))
            ->assertOk()->assertSeeInOrder(['TKQA203', 'EKQA202', 'RJQA201'])->assertDontSee('RJQA204');
        $this->get(route('compare', ['flights' => $offers->modelKeys()]))
            ->assertOk()->assertSee('Royal Jordanian')->assertSee('Emirates')->assertSee('Turkish Airlines');
        $admin = User::where('email', 'admin@demo.example.test')->firstOrFail();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/admin/bookings')->assertOk();
    }

    public function test_demo_refuses_production(): void
    {
        $this->app->instance('env', 'production');
        $this->expectException(\RuntimeException::class);
        app(DemoSeeder::class)->run();
    }
}
