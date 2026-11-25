<?php

namespace Tests\Feature;

use App\Models\{Airline, Airport, City, Country, CreditTransaction, Customer, Flight, FlightClass, Seat, User};
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Dev3FlowTest extends TestCase
{
    use RefreshDatabase;

    private function inventory(): array
    {
        $country = Country::create(['name' => 'Jordan', 'code' => 'JO', 'currency' => 'JOD', 'language' => 'Arabic']);
        $city = City::create(['country_id' => $country->id, 'name' => 'Amman']);
        $other = City::create(['country_id' => $country->id, 'name' => 'Aqaba']);
        $from = Airport::create(['city_id' => $city->id, 'code' => 'AMM', 'name' => 'Queen Alia']);
        $to = Airport::create(['city_id' => $other->id, 'code' => 'AQJ', 'name' => 'King Hussein']);
        $airline = Airline::create(['name' => 'Test Air', 'code' => 'TA', 'country' => 'Jordan', 'status' => 'active']);
        $classes = [];
        foreach ([100, 200, 150] as $index => $price) {
            $return = $index === 2;
            $date = now()->addDays($return ? 7 : 5)->startOfDay()->addHours(12 + $index);
            $flight = Flight::create(['airline_id' => $airline->id, 'flight_number' => 'TA'.($index + 1),
                'origin_airport_id' => $return ? $to->id : $from->id, 'destination_airport_id' => $return ? $from->id : $to->id,
                'departure_at' => $date, 'arrival_at' => $date->copy()->addHour(), 'stops' => 0, 'status' => 'scheduled']);
            $class = FlightClass::create(['flight_id' => $flight->id, 'class' => 'economy', 'price' => $price,
                'seat_rows' => 1, 'seats_per_row' => 3, 'total_seats' => 3, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 20]);
            foreach (['1A', '1B', '1C'] as $number) {
                Seat::create(['flight_class_id' => $class->id, 'seat_number' => $number, 'status' => 'available']);
            }
            $classes[] = $class;
        }
        return $classes;
    }

    private function filters(array $extra = []): array
    {
        return array_merge(['from' => 'AMM', 'to' => 'AQJ', 'depart' => now()->addDays(5)->toDateString(),
            'trip' => 'one-way', 'adults' => 1, 'youth' => 0, 'children' => 0, 'infants' => 0], $extra);
    }

    public function test_landing_search_details_comparison_and_round_trip_selection_over_http(): void
    {
        [$first, $second, $return] = $this->inventory();
        $this->get('/')->assertOk()->assertSee('Queen Alia')->assertDontSee('name="trip" value="multi-city"', false);
        $this->get('/flights')->assertOk()->assertViewIs('flights.index')->assertSee('landing-search')->assertDontSee('bootstrap.min.css');
        $this->get(route('flights.search', $this->filters()))->assertOk()->assertSee('TA1')->assertSee('TA2')->assertDontSee('TA3');
        $this->get(route('flights.search', $this->filters(['budget' => 150])))->assertOk()->assertSee('TA1')->assertDontSee('TA2');
        $this->get(route('flights.show', $first->flight_id))->assertOk()->assertViewIs('flights.show')->assertSee('Queen Alia')->assertSee('100.00');
        $this->get(route('compare', ['flights' => [$first->flight_id, $second->flight_id]]))->assertOk()->assertViewIs('flights.compare')->assertSee('TA1')->assertSee('TA2');
        $round = $this->filters(['trip' => 'round-trip', 'return' => now()->addDays(7)->toDateString()]);
        $this->get(route('flights.search', array_merge($round, ['leg' => 'return', 'outbound_class_id' => $first->id])))
            ->assertOk()->assertSee('TA3')->assertDontSee('TA2');
        $this->post(route('flights.selection'), array_merge($round, ['outbound_class_id' => $first->id, 'return_class_id' => $return->id]))
            ->assertOk()->assertViewIs('flights.selection')->assertSessionHas('dev3.search_selection.outbound.flight_class_id', $first->id)
            ->assertSessionHas('dev3.search_selection.return.flight_class_id', $return->id);
        $first->seats()->update(['status' => 'booked']);
        $this->post(route('flights.selection'), array_merge($this->filters(), ['outbound_class_id' => $first->id]))
            ->assertSessionHasErrors('selection');
    }

    public function test_credit_changes_are_audited_idempotent_and_cannot_overdraw(): void
    {
        $admin = User::factory()->create();
        $admin->superAdmin()->create([]);
        $customer = User::factory()->create()->customer()->create(['credit_balance' => '0.00']);
        $credits = app(CreditService::class);
        $key = (string) Str::uuid();
        $entry = $credits->addCredits($customer->id, '10.10', $key, 'Correction', $admin->id);
        $this->assertSame($entry->id, $credits->addCredits($customer->id, '10.10', $key, 'Correction', $admin->id)->id);
        $credits->deductCredits($customer->id, '0.10', (string) Str::uuid(), 'Correction', $admin->id);
        $this->assertSame('10.00', $customer->fresh()->credit_balance);
        try {
            $credits->deductCredits($customer->id, '10.01', (string) Str::uuid(), 'Correction', $admin->id);
            $this->fail('Overdraft accepted');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }
        $this->assertSame(2, CreditTransaction::count());
        $this->actingAs($admin)->post(route('admin.credits.update', $customer), [
            'action' => 'adjust', 'amount' => '2.25', 'reason' => 'Support correction', 'operation_id' => (string) Str::uuid(),
        ])->assertRedirect(route('admin.credits'));
        $this->assertSame('2.25', $customer->fresh()->credit_balance);
        $this->get(route('admin.credits'))->assertOk()->assertViewIs('super-admin.panel')->assertSee('Support correction')->assertDontSee('Staff interface preview.');
    }

    public function test_operation_key_cannot_be_reused_with_different_amount(): void
    {
        $admin = User::factory()->create();
        $admin->superAdmin()->create([]);
        $customer = User::factory()->create()->customer()->create(['credit_balance' => '0.00']);
        $key = (string) Str::uuid();
        app(CreditService::class)->addCredits($customer->id, '5', $key, 'Correction', $admin->id);
        $this->expectException(ValidationException::class);
        app(CreditService::class)->addCredits($customer->id, '6', $key, 'Correction', $admin->id);
    }

    public function test_countries_crud_dashboard_and_information_pages_over_http(): void
    {
        $admin = User::factory()->create();
        $admin->superAdmin()->create([]);
        $this->actingAs($admin);
        $data = ['name' => 'Jordan', 'code' => 'jo', 'currency' => 'JOD', 'language' => 'Arabic'];
        $this->get(route('admin.countries.create'))->assertOk()->assertSee('Platform Admin navigation');
        $this->post(route('admin.countries.store'), $data)->assertRedirect(route('admin.countries'));
        $country = Country::firstOrFail();
        $this->assertSame('JO', $country->code);
        $this->get(route('admin.countries.edit', $country))->assertOk()->assertSee('Jordan');
        $this->put(route('admin.countries.update', $country), array_merge($data, ['name' => 'Jordan Updated']))
            ->assertRedirect(route('admin.countries'));
        $this->get(route('admin.countries'))->assertOk()->assertSee('Jordan Updated');
        City::create(['country_id' => $country->id, 'name' => 'Amman']);
        $this->delete(route('admin.countries.destroy', $country))->assertSessionHasErrors('country');
        $this->get(route('admin.dashboard'))->assertOk()->assertViewIs('super-admin.panel')->assertSee('Platform Admin Dashboard')->assertDontSee('Staff interface preview.');
        $this->get(route('faq'))->assertOk()->assertViewIs('pages.information');
        $this->get(route('terms'))->assertOk()->assertViewIs('pages.information');
        $empty = Country::create(['name' => 'Empty', 'code' => 'AE', 'currency' => 'AED', 'language' => 'Arabic']);
        $this->delete(route('admin.countries.destroy', $empty))->assertRedirect(route('admin.countries'));
        $this->assertModelMissing($empty);
    }

    public function test_review_ownership_completion_and_duplicate_protection_over_http(): void
    {
        [$class] = $this->inventory();
        $owner = User::factory()->create();
        $customer = $owner->customer()->create(['credit_balance' => '0.00']);
        $booking = $customer->bookings()->create(['code' => 'TESTREVIEW', 'trip_type' => 'one_way', 'status' => 'completed',
            'subtotal' => 100, 'taxes' => 0, 'fees' => 0, 'total' => 100, 'credits_used' => 0, 'amount_paid' => 100,
            'contact_email' => $owner->email]);
        $segment = $booking->bookingFlights()->create(['flight_id' => $class->flight_id, 'flight_class_id' => $class->id, 'direction' => 'outbound']);
        $this->actingAs($owner)->get(route('my.reviews.create', $segment))->assertSessionHasErrors('review');
        $class->flight->update(['status' => 'completed', 'departure_at' => now()->subHours(3), 'arrival_at' => now()->subHour()]);
        $this->get(route('my.reviews.create', $segment))->assertOk()->assertViewIs('customer.account')->assertDontSee('Sample account.')->assertDontSee('Preview review');
        $this->get(route('my.review', $booking->code))->assertRedirect(route('my.reviews.create', $segment->id));
        $other = User::factory()->create();
        $other->customer()->create(['credit_balance' => '0.00']);
        $this->actingAs($other)->get(route('my.reviews.create', $segment))->assertNotFound();
        $this->actingAs($owner)->post(route('my.reviews.store', $segment), ['rating' => 5, 'comment' => 'Good flight'])
            ->assertRedirect(route('my.bookings'));
        $this->post(route('my.reviews.store', $segment), ['rating' => 4, 'comment' => 'Duplicate'])->assertSessionHasErrors('review');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_assistant_uses_existing_page_and_duplicate_view_directory_is_gone(): void
    {
        $user = User::factory()->create();
        $user->customer()->create(['credit_balance' => '0.00']);
        config(['services.dev3_ai.endpoint' => null, 'services.dev3_ai.key' => null]);
        $this->actingAs($user)->get(route('assistant'))->assertOk()->assertViewIs('pages.assistant')
            ->assertSee('AI integration is currently unavailable.')->assertSee('Conversation history');
        $this->get(route('my.assistant'))->assertOk()->assertViewIs('pages.assistant');
        $this->assertDirectoryDoesNotExist(resource_path('views/omar'));
    }
}
