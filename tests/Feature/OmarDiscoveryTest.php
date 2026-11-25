<?php

namespace Tests\Feature;

use App\Http\Controllers\Customer\AiConversationController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\PublicFlightController;
use App\Http\Controllers\PublicSite\CompareController;
use App\Http\Controllers\SuperAdmin\CreditController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Models\Booking;
use App\Models\BookingFlight;
use App\Models\Customer;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\User;
use App\Services\AiService;
use App\Services\CreditService;
use App\Services\FlightSearchService;
use App\Services\PassportExtraction;
use App\Services\PrePaymentCheck;
use App\Services\SearchSelectionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\PresenceVerifierInterface;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

// No migrations, schema commands, application database writes or real HTTP calls.
class OmarDiscoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $presence = Mockery::mock(PresenceVerifierInterface::class);
        $presence->shouldReceive('getCount')->andReturn(1);
        app('validator')->setPresenceVerifier($presence);
    }

    private function filters(array $changes = []): array
    {
        return array_merge(['from' => 'AMM', 'to' => 'DXB', 'depart' => now()->addDays(5)->toDateString()], $changes);
    }

    public function test_search_normalizes_airports_and_supplies_defaults(): void
    {
        $filters = (new FlightSearchService)->validateFilters($this->filters(['from' => ' amm ', 'to' => 'dxb']));
        $this->assertSame('AMM', $filters['from']);
        $this->assertSame('DXB', $filters['to']);
        $this->assertSame('one_way', $filters['trip']);
        $this->assertSame(1, $filters['passengers']);
        $this->assertSame('outbound', $filters['leg']);
        $this->assertSame(0, $filters['min_price']);
    }

    public function test_city_search_is_an_alternative_to_airport_search(): void
    {
        $filters = (new FlightSearchService)->validateFilters([
            'origin_city_id' => 1, 'destination_city_id' => 2, 'depart' => now()->addDays(5)->toDateString(),
        ]);
        $this->assertSame(1, $filters['origin_city_id']);
        $this->assertSame(2, $filters['destination_city_id']);
    }

    public function test_invalid_search_filters_are_rejected_before_querying(): void
    {
        $search = new FlightSearchService;
        foreach ([
            ['to' => 'AMM'],
            ['depart' => now()->subDay()->toDateString()],
            ['depart' => 'not-a-date'],
            ['trip' => 'round_trip'],
            ['trip' => 'round_trip', 'return' => now()->addDay()->toDateString()],
            ['passengers' => 0], ['passengers' => 10], ['passengers' => '1 OR 1=1'],
            ['cabin' => 'premium'], ['sort' => 'price; DROP TABLE flights'],
            ['budget' => -1], ['budget' => 5, 'min_price' => 10],
            ['origin_city_id' => 4],
            ['from' => ['AMM']],
        ] as $changes) {
            try {
                $search->validateFilters($this->filters($changes));
                $this->fail('Invalid search filters were accepted: '.json_encode($changes));
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_missing_airport_is_rejected(): void
    {
        $presence = Mockery::mock(PresenceVerifierInterface::class);
        $presence->shouldReceive('getCount')->andReturn(0);
        app('validator')->setPresenceVerifier($presence);
        $this->expectException(ValidationException::class);
        (new FlightSearchService)->validateFilters($this->filters(['from' => 'ZZZ']));
    }

    public function test_round_trip_search_does_not_require_separate_return_leg_selection(): void
    {
        $filters = (new FlightSearchService)->validateFilters($this->filters([
            'trip' => 'round_trip', 'return' => now()->addDays(9)->toDateString(), 'leg' => 'return',
        ]));

        $this->assertSame('round_trip', $filters['trip']);
        $this->assertSame('outbound', $filters['leg']);
    }

    public function test_available_query_applies_airline_status_date_price_cabin_and_seat_gates(): void
    {
        // Generating SQL does not open a PDO connection or execute the query.
        $search = new FlightSearchService;
        $query = $search->query($search->validateFilters($this->filters([
            'cabin' => 'business', 'min_price' => '100.00', 'budget' => '500.00', 'passengers' => 3,
        ])));
        $sql = $query->toSql();
        foreach (['airlines', 'flight_classes', 'seats', 'departure_at', 'origin_airport_id', 'destination_airport_id', 'minimum_price'] as $column) {
            $this->assertStringContainsString($column, $sql);
        }
        foreach (['scheduled', 'delayed', 'active', 'available', 'business', '100.00', '500.00', 'AMM', 'DXB'] as $binding) {
            $this->assertContains($binding, $query->getBindings());
        }
        $this->assertMatchesRegularExpression('/>= 3/', $sql);
    }

    public function test_selection_rejects_missing_outbound_and_duplicate_return_cabins(): void
    {
        foreach ([['adults' => 1], ['adults' => 1, 'trip' => 'round_trip', 'return' => now()->addDays(9)->toDateString(), 'outbound_class_id' => 5, 'return_class_id' => 5]] as $changes) {
            try {
                (new SearchSelectionService)->select($this->filters($changes), new FlightSearchService);
                $this->fail('Invalid selection was accepted.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_round_trip_selection_returns_database_ids_and_prices_instead_of_submitted_prices(): void
    {
        $search = Mockery::mock(FlightSearchService::class)->makePartial();

        $outboundFlight = new Flight;
        $outboundFlight->id = 10;
        $outboundClass = new FlightClass(['class' => 'economy', 'price' => '150.25']);
        $outboundClass->id = 20;
        $outboundFlight->setRelation('flightClasses', collect([$outboundClass]));

        $returnFlight = new Flight;
        $returnFlight->id = 11;
        $returnClass = new FlightClass(['class' => 'economy', 'price' => '180.50']);
        $returnClass->id = 21;
        $returnFlight->setRelation('flightClasses', collect([$returnClass]));
        $outboundFlight->setRelation('returnFlight', $returnFlight);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereHas')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn($outboundFlight);
        $search->shouldReceive('query')->once()->andReturn($query);

        $selection = (new SearchSelectionService)->select($this->filters([
            'trip' => 'round_trip', 'return' => now()->addDays(9)->toDateString(),
            'outbound_class_id' => 20, 'price' => '0.01',
            'adults' => '1', 'youth' => '1', 'children' => '1', 'infants' => '0',
            'passenger_counts' => ['adults' => 99],
        ]), $search);

        $this->assertSame(['flight_id' => 10, 'flight_class_id' => 20, 'price' => '150.25'], $selection['outbound']);
        $this->assertSame(['flight_id' => 11, 'flight_class_id' => 21, 'price' => '180.50'], $selection['return']);
        $this->assertArrayNotHasKey('price', $selection['filters']);
        $this->assertSame(['adults' => 1, 'youth' => 1, 'children' => 1, 'infants' => 0], $selection['passenger_counts']);
        $this->assertSame(3, $selection['passengers']);
    }

    public function test_selection_rejects_total_only_search_instead_of_inventing_age_groups(): void
    {
        $search = Mockery::mock(FlightSearchService::class)->makePartial();
        $search->shouldNotReceive('query');

        try {
            (new SearchSelectionService)->select($this->filters(['passengers' => 3, 'outbound_class_id' => 20]), $search);
            $this->fail('Unknown age groups were accepted for booking handoff.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('adults', $exception->errors());
        }
    }

    public function test_one_way_selection_always_contains_four_integer_counts_and_null_return(): void
    {
        $search = Mockery::mock(FlightSearchService::class)->makePartial();
        $flight = new Flight;
        $flight->id = 10;
        $class = new FlightClass(['price' => '150.25']);
        $class->id = 20;
        $flight->setRelation('flightClasses', collect([$class]));
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereHas')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn($flight);
        $search->shouldReceive('query')->once()->withArgs(fn ($filters) => $filters['passengers'] === 2)->andReturn($query);

        $selection = (new SearchSelectionService)->select($this->filters(['adults' => '2', 'outbound_class_id' => 20]), $search);

        $this->assertSame(['adults' => 2, 'youth' => 0, 'children' => 0, 'infants' => 0], $selection['passenger_counts']);
        $this->assertSame('one_way', $selection['trip']);
        $this->assertSame(2, $selection['passengers']);
        $this->assertNull($selection['return']);
    }

    public function test_selection_controller_saves_the_breakdown_under_the_existing_session_key(): void
    {
        $selection = [
            'trip' => 'one_way', 'passengers' => 2,
            'passenger_counts' => ['adults' => 1, 'youth' => 0, 'children' => 1, 'infants' => 0],
            'outbound' => ['flight_id' => 10, 'flight_class_id' => 20, 'price' => '150.25'],
            'return' => null, 'filters' => [],
        ];
        $service = Mockery::mock(SearchSelectionService::class);
        $service->shouldReceive('select')->once()->andReturn($selection);
        $request = Request::create('/flights/selection', 'POST');
        $request->setLaravelSession(app('session')->driver());

        $view = (new PublicFlightController)->select($request, $service, new FlightSearchService);

        $this->assertSame($selection, $request->session()->get('dev3.search_selection'));
        $this->assertSame($selection, $view->getData()['selection']);
    }

    public function test_compare_rejects_duplicates_invalid_ids_and_more_than_three(): void
    {
        foreach ([[1], [1, 1], [1, 2, 3, 4], [1, -5], [1, 'bad'], [[1], 2]] as $ids) {
            try {
                (new CompareController)->index(Request::create('/compare', 'GET', ['flights' => $ids]), new FlightSearchService);
                $this->fail('Invalid comparison was accepted.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_credit_mutations_reject_invalid_operation_keys_before_database_access(): void
    {
        $credits = new CreditService;
        foreach (['addCredits', 'deductCredits', 'adjustBalance'] as $method) {
            try {
                $credits->$method(1, '10.00', 'operation-key', 'Test reason', 2);
                $this->fail('Invalid operation key was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('operation_id', $exception->errors());
            }
        }
    }

    public function test_admin_and_customer_endpoints_deny_guests_and_wrong_roles(): void
    {
        $user = new User;
        $user->setRelation('superAdmin', null);
        $user->setRelation('customer', null);
        foreach ([null, $user] as $identity) {
            $request = Request::create('/');
            $request->setUserResolver(fn () => $identity);
            $calls = [
                fn () => (new DashboardController)->index($request),
                fn () => (new CreditController)->index($request),
                fn () => (new CreditController)->update($request, new Customer, new CreditService),
                fn () => (new ReviewController)->create($request, 1),
                fn () => (new ReviewController)->store($request, 1),
                fn () => (new AiConversationController)->index($request, new AiService),
                fn () => (new AiConversationController)->store($request, new AiService, new FlightSearchService),
                fn () => (new AiConversationController)->show($request, 1),
            ];
            foreach ($calls as $call) {
                try {
                    $call();
                    $this->fail('Protected endpoint allowed the wrong role.');
                } catch (HttpException $exception) {
                    $this->assertSame(403, $exception->getStatusCode());
                }
            }
        }
    }

    public function test_unpaid_booking_is_not_reviewable(): void
    {
        $method = new \ReflectionMethod(ReviewController::class, 'checkEligibility');
        $segment = new BookingFlight;
        $booking = new Booking(['status' => 'pending']);
        $booking->setRelation('payments', collect([(object) ['status' => 'pending']]));
        $segment->setRelation('booking', $booking);
        $segment->setRelation('flight', new Flight(['status' => 'scheduled', 'arrival_at' => now()->addDay()]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Reviews are available after your booking is paid.');
        $method->invoke(new ReviewController, $segment);
    }

    public function test_ai_is_unavailable_without_provider_configuration(): void
    {
        config(['services.dev3_ai' => []]);
        $this->assertFalse((new AiService)->available());
        try {
            (new AiService)->reply('tips', 'Airport advice');
            $this->fail('Unconfigured provider was used.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('not been configured', $exception->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_duplicate_review_is_rejected_for_paid_booking(): void
    {
        $segment = Mockery::mock(BookingFlight::class)->makePartial();
        $booking = new Booking(['customer_id' => 42, 'status' => 'confirmed']);
        $booking->setRelation('payments', collect([(object) ['status' => 'succeeded']]));
        $segment->setRelation('booking', $booking);
        $segment->setRelation('flight', new Flight(['status' => 'scheduled', 'arrival_at' => now()->addDay()]));
        $reviews = Mockery::mock(HasMany::class);
        $reviews->shouldReceive('where')->once()->with('customer_id', 42)->andReturnSelf();
        $reviews->shouldReceive('exists')->once()->andReturn(true);
        $segment->shouldReceive('reviews')->once()->andReturn($reviews);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('already reviewed');
        (new \ReflectionMethod(ReviewController::class, 'checkEligibility'))->invoke(new ReviewController, $segment);
    }

    public function test_conversation_reads_are_scoped_to_the_authenticated_customer(): void
    {
        $customer = Mockery::mock(Customer::class)->makePartial();
        $conversations = Mockery::mock(HasMany::class);
        $conversations->shouldReceive('findOrFail')->once()->with(99)
            ->andThrow(new ModelNotFoundException);
        $customer->shouldReceive('aiConversations')->once()->andReturn($conversations);
        $user = new User;
        $user->setRelation('customer', $customer);
        $request = Request::create('/my/assistant/99');
        $request->setUserResolver(fn () => $user);

        $this->expectException(ModelNotFoundException::class);
        (new AiConversationController)->show($request, 99);
    }

    public function test_sample_seeder_has_valid_php_syntax(): void
    {
        $this->assertNotEmpty(token_get_all(file_get_contents(database_path('seeders/CountrySeeder.php')), TOKEN_PARSE));
    }

    private function configureAi(): void
    {
        config(['services.dev3_ai.endpoint' => 'https://gateway.example.test/tips', 'services.dev3_ai.key' => 'fake-test-key']);
    }

    public function test_ai_renders_only_approved_tips_and_ignores_invented_prices(): void
    {
        $this->configureAi();
        Http::fake(['gateway.example.test/*' => Http::response([
            'tip_keys' => ['check_baggage', 'check_baggage', 'verify_itinerary'],
            'price' => '1.00', 'flight_id' => 999, 'message' => 'Pay now!',
        ])]);
        $tips = (new AiService)->reply('compare', 'Compare flights');
        $this->assertSame([AiService::TIPS['check_baggage'], AiService::TIPS['verify_itinerary']], $tips);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer fake-test-key')
            && $request['mode'] === 'compare' && ! isset($request['passport']));
    }

    public function test_ai_provider_errors_are_reported(): void
    {
        $this->configureAi();
        Http::fake(['gateway.example.test/*' => Http::response([], 503)]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('temporarily unavailable');
        (new AiService)->reply('tips', 'Travel tips');
    }

    public function test_ai_timeout_is_reported(): void
    {
        $this->configureAi();
        Http::fake(fn () => throw new ConnectionException('timeout'));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('could not be reached');
        (new AiService)->reply('tips', 'Travel tips');
    }

    public function test_ai_unknown_tip_keys_are_rejected(): void
    {
        $this->configureAi();
        Http::fake(['gateway.example.test/*' => Http::response(['tip_keys' => ['execute_payment']])]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unsupported response');
        (new AiService)->reply('tips', 'Pay now');
    }

    public function test_ai_malformed_response_is_rejected(): void
    {
        $this->configureAi();
        Http::fake(['gateway.example.test/*' => Http::response('not-json', 200)]);
        $this->expectException(\RuntimeException::class);
        (new AiService)->reply('tips', 'Travel tips');
    }

    public function test_ai_refuses_insecure_provider_endpoints(): void
    {
        config(['services.dev3_ai.endpoint' => 'http://gateway.example.test', 'services.dev3_ai.key' => 'test']);
        $this->assertFalse((new AiService)->available());
    }

    public function test_dev4_interfaces_are_contracts_without_fake_implementations(): void
    {
        $this->assertTrue(interface_exists(PassportExtraction::class));
        $this->assertTrue(interface_exists(PrePaymentCheck::class));
        $this->assertTrue(app()->bound(PassportExtraction::class));
        $this->assertFalse(app()->bound(PrePaymentCheck::class));
    }

    public function test_all_new_blade_templates_compile(): void
    {
        $paths = [resource_path('views/layouts/app.blade.php'), resource_path('views/staff/panel.blade.php'), resource_path('views/customer/account.blade.php')];
        foreach (['flights', 'pages', 'customer/assistant', 'super-admin/countries', 'super-admin/dashboard', 'super-admin/credits'] as $folder) {
            $paths = array_merge($paths, glob(resource_path('views/'.$folder.'/*.blade.php')));
        }
        $this->assertGreaterThan(10, count($paths));
        foreach ($paths as $path) {
            $compiled = app('blade.compiler')->compileString(file_get_contents($path));
            $this->assertNotEmpty(token_get_all($compiled, TOKEN_PARSE), $path);
        }
    }

    public function test_every_documented_route_has_a_public_controller_action(): void
    {
        $routes = file_get_contents(base_path('routes/omar-routes.txt'));
        token_get_all("<?php\n".$routes, TOKEN_PARSE);
        preg_match_all("/\[(\\\\App\\\\Http\\\\Controllers\\\\[A-Za-z\\\\]+)::class, '([a-zA-Z]+)'\]/", $routes, $matches, PREG_SET_ORDER);
        $this->assertGreaterThan(15, count($matches));
        foreach ($matches as $match) {
            $this->assertTrue((new \ReflectionMethod($match[1], $match[2]))->isPublic());
        }
    }
}
