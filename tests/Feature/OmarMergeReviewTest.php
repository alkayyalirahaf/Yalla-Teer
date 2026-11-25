<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\AirlineAdmin;
use App\Models\Customer;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Services\AuthService;
use App\Services\FlightSearchService;
use Illuminate\Http\Request;
use Illuminate\Validation\PresenceVerifierInterface;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

// Merge regression checks: no migrations, application DB writes or external payments.
class OmarMergeReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $presence = Mockery::mock(PresenceVerifierInterface::class);
        $presence->shouldReceive('getCount')->andReturn(1);
        app('validator')->setPresenceVerifier($presence);
    }

    private function filters(array $changes = []): array
    {
        return array_merge(['from' => 'AMM', 'to' => 'DXB', 'depart' => now()->addDays(5)->toDateString()], $changes);
    }

    public function test_landing_one_way_search_counts_all_seated_travelers(): void
    {
        $filters = (new FlightSearchService)->validateFilters($this->filters([
            'trip' => 'one-way', 'adults' => '2', 'youth' => '1', 'children' => '2', 'infants' => '0',
        ]));

        $this->assertSame('one_way', $filters['trip']);
        $this->assertSame(5, $filters['passengers']);
        $this->assertSame(2, $filters['adults']);
        $this->assertSame($filters, (new FlightSearchService)->validateFilters($filters));
    }

    public function test_landing_round_trip_and_legacy_sort_are_normalized(): void
    {
        $filters = (new FlightSearchService)->validateFilters($this->filters([
            'trip' => 'round-trip', 'return' => now()->addDays(9)->toDateString(), 'sort' => 'latest',
        ]));
        $this->assertSame('round_trip', $filters['trip']);
        $this->assertSame('departure_desc', $filters['sort']);
    }

    public function test_inconsistent_or_invalid_traveler_counts_are_rejected(): void
    {
        foreach ([
            ['adults' => 2, 'children' => 1, 'passengers' => 1],
            ['adults' => 8, 'children' => 2],
            ['adults' => 0],
            ['adults' => 1, 'children' => -1],
            ['children' => 2],
            ['adults' => [2]],
        ] as $changes) {
            try {
                (new FlightSearchService)->validateFilters($this->filters($changes));
                $this->fail('Invalid traveler counts were accepted.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_infant_search_requires_an_approved_seat_policy(): void
    {
        try {
            (new FlightSearchService)->validateFilters($this->filters(['adults' => 1, 'infants' => 1]));
            $this->fail('An infant seat policy was silently assumed.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('infants', $exception->errors());
            $this->assertStringContainsString('not configured', $exception->errors()['infants'][0]);
        }
    }

    public function test_admin_login_form_renders_at_its_existing_route(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertViewIs('auth.login')
            ->assertSee('Platform Admin Login')
            ->assertSee('action="'.route('admin.login.submit').'"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_admin_login_uses_the_shared_auth_service_and_super_admin_role(): void
    {
        $this->mock(AuthService::class)->shouldReceive('attempt')->once()
            ->with(['email' => 'admin@example.test', 'password' => 'TestPassword123!'], AuthService::ROLE_SUPER_ADMIN)
            ->andReturn(true);

        $this->post(route('admin.login.submit'), [
            'email' => ' ADMIN@example.test ', 'password' => 'TestPassword123!',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_login_failure_keeps_email_and_never_flashes_password(): void
    {
        $this->mock(AuthService::class)->shouldReceive('attempt')->once()->andReturn(false);
        $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => 'admin@example.test', 'password' => 'WrongPassword',
        ])->assertRedirect(route('admin.login'))->assertSessionHasErrors('email')
            ->assertSessionHasInput('email', 'admin@example.test');

        $this->assertArrayNotHasKey('password', session()->getOldInput());
    }

    public function test_admin_login_is_rate_limited_even_without_route_changes(): void
    {
        $this->mock(AuthService::class)->shouldReceive('attempt')->times(5)->andReturn(false);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.submit'), [
                'email' => 'rate@example.test', 'password' => 'WrongPassword',
            ])->assertRedirect();
        }

        $this->post(route('admin.login.submit'), [
            'email' => 'rate@example.test', 'password' => 'WrongPassword',
        ])->assertStatus(429);
    }

    public function test_airline_login_form_uses_its_named_post_route(): void
    {
        $this->get(route('airline.login'))->assertOk()
            ->assertSee('action="'.route('airline.login.submit').'"', false);
    }

    public function test_airline_login_uses_normalized_credentials_role_and_remember_option(): void
    {
        $this->mock(AuthService::class)->shouldReceive('attempt')->once()
            ->with(['email' => 'staff@example.test', 'password' => 'TestPassword123!'], AuthService::ROLE_AIRLINE_ADMIN, true)
            ->andReturn(false);

        $this->from(route('airline.login'))->post(route('airline.login.submit'), [
            'email' => ' STAFF@example.test ', 'password' => 'TestPassword123!', 'remember' => 1,
        ])->assertRedirect(route('airline.login'))->assertSessionHasErrors('email');
    }

    public function test_integrated_public_forms_render_with_working_post_targets(): void
    {
        foreach (['/login', '/register', '/airline/login', '/contact'] as $path) {
            $this->get($path)->assertOk()->assertSee('method="POST"', false)
                ->assertDontSee('temporarily unavailable');
        }
    }

    public function test_authenticated_layout_has_a_logout_form(): void
    {
        $user = new User;
        $user->id = 123;
        // The home page overrides navigation, so use the shared layout's login page.
        $this->actingAs($user)->get('/login')->assertOk()
            ->assertSee('action="'.route('logout').'"', false);
    }

    private function airlineUser(string $status, bool $alsoCustomer = false): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('load')->with(['customer', 'airlineAdmin', 'superAdmin'])->andReturnSelf();
        $admin = new AirlineAdmin;
        $admin->setRelation('airline', new Airline(['status' => $status]));
        $user->setRelation('airlineAdmin', $admin);
        $user->setRelation('customer', $alsoCustomer ? new Customer : null);
        $user->setRelation('superAdmin', null);

        return $user;
    }

    public function test_role_resolution_rejects_inactive_airlines_and_conflicting_profiles(): void
    {
        $auth = new AuthService(Request::create('/'));
        $this->assertSame(AuthService::ROLE_AIRLINE_ADMIN, $auth->resolveRole($this->airlineUser('active')));
        $this->assertNull($auth->resolveRole($this->airlineUser('pending')));
        $this->assertNull($auth->resolveRole($this->airlineUser('suspended')));
        $this->assertNull($auth->resolveRole($this->airlineUser('active', true)));
    }

    public function test_customer_and_super_admin_roles_do_not_require_an_airline(): void
    {
        $auth = new AuthService(Request::create('/'));
        foreach (['customer' => [new Customer, null, AuthService::ROLE_CUSTOMER],
            'superAdmin' => [null, new SuperAdmin, AuthService::ROLE_SUPER_ADMIN]] as [$customer, $admin, $expected]) {
            $user = Mockery::mock(User::class)->makePartial();
            $user->shouldReceive('load')->andReturnSelf();
            $user->setRelation('customer', $customer);
            $user->setRelation('superAdmin', $admin);
            $user->setRelation('airlineAdmin', null);
            $this->assertSame($expected, $auth->resolveRole($user));
        }
    }

    public function test_registered_controller_routes_have_existing_public_methods(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            $action = $route->getActionName();
            if ($action === 'Closure') {
                continue;
            }
            [$class, $method] = array_pad(explode('@', $action), 2, '__invoke');
            $this->assertTrue(method_exists($class, $method), $action);
            $this->assertTrue((new \ReflectionMethod($class, $method))->isPublic(), $action);
        }
    }

    public function test_stripe_sdk_is_installed_and_invalid_webhook_signatures_are_rejected(): void
    {
        $this->assertTrue(class_exists(\Stripe\StripeClient::class));
        config(['services.stripe.webhook_secret' => 'whsec_merge_test']);
        $request = Request::create('/stripe/webhook', 'POST', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=invalid',
        ], '{}');

        $response = (new \App\Http\Controllers\Webhooks\StripeWebhookController)->handle(
            $request, new \App\Services\PaymentService,
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_required_merged_routes_use_the_expected_controller_actions(): void
    {
        foreach ([
            'login.store' => [\App\Http\Controllers\Auth\CustomerSessionController::class, 'store'],
            'register.store' => [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store'],
            'logout' => [\App\Http\Controllers\Auth\CustomerSessionController::class, 'destroy'],
            'airline.login.submit' => [\App\Http\Controllers\Auth\AirlineAdminSessionController::class, 'store'],
            'contact.store' => [\App\Http\Controllers\PublicSite\ContactMessageController::class, 'store'],
            'book.payment' => [\App\Http\Controllers\Customer\PaymentController::class, 'show'],
            'book.payment.intent' => [\App\Http\Controllers\Customer\PaymentController::class, 'intent'],
            'book.payment.return' => [\App\Http\Controllers\Customer\PaymentController::class, 'return'],
            'stripe.webhook' => [\App\Http\Controllers\Webhooks\StripeWebhookController::class, 'handle'],
            'admin.airports' => [\App\Http\Controllers\SuperAdmin\AirportController::class, 'index'],
            'my.credits' => [\App\Http\Controllers\Customer\CreditWalletController::class, 'index'],
        ] as $name => [$controller, $method]) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertNotNull($route, $name);
            $this->assertSame($controller.'@'.$method, $route->getActionName());
        }
    }

    public function test_guests_are_redirected_to_the_correct_role_login(): void
    {
        foreach ([
            '/my/bookings' => 'login', '/my/credits' => 'login',
            '/book/payment' => 'login', '/assistant' => 'login',
            '/admin' => 'admin.login', '/admin/airports' => 'admin.login',
            '/admin/countries' => 'admin.login', '/airline' => 'airline.login',
        ] as $path => $login) {
            $this->get($path)->assertRedirect(route($login));
        }
    }

    public function test_webhook_route_rejects_invalid_signature_without_authentication(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_merge_route_test']);
        $this->postJson(route('stripe.webhook'), [], [
            'Stripe-Signature' => 't='.time().',v1=invalid',
        ])->assertStatus(400);
        $this->assertGuest();
    }
}
