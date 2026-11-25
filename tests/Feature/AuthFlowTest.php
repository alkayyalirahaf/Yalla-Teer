<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAuthController;
use App\Models\Airline;
use App\Models\Customer;
use App\Models\User;
use App\Services\AuthService;
use Database\Seeders\AuthSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_user_and_zero_balance_customer(): void
    {
        $response = $this->post('/register', $this->validRegistration());

        $response->assertRedirect(route('my.bookings'));

        $user = User::where('email', 'new.customer@example.test')->firstOrFail();
        $customer = $user->customer()->firstOrFail();

        $this->assertSame('0.00', $customer->credit_balance);
        $this->assertAuthenticatedAs($user);
    }

    public function test_auth_seeder_creates_three_distinct_role_accounts_and_their_airline(): void
    {
        config()->set('auth_seeds', [
            'customer' => ['name' => 'Seed Customer', 'email' => 'seed-customer@example.test', 'password' => 'SeedCustomer123!'],
            'super_admin' => ['name' => 'Seed Platform Admin', 'email' => 'seed-admin@example.test', 'password' => 'SeedAdmin123!'],
            'airline_admin' => ['name' => 'Seed Airline Admin', 'email' => 'seed-airline-admin@example.test', 'password' => 'SeedAirline123!'],
            'airline' => ['name' => 'Seed Airline', 'code' => 'SDR', 'country' => 'Jordan'],
        ]);

        $this->seed(AuthSeeder::class);
        $this->seed(AuthSeeder::class);

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('super_admins', 1);
        $this->assertDatabaseCount('airline_admins', 1);
        $this->assertDatabaseCount('airlines', 1);

        $customer = User::where('email', 'seed-customer@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('SeedCustomer123!', $customer->password));
        $this->assertSame('0.00', $customer->customer->credit_balance);
    }

    public function test_custom_auth_forms_render_with_csrf_and_post_targets(): void
    {
        $this->get('/login')->assertOk()->assertSee('action="'.route('login').'"', false)->assertSee('name="_token"', false);
        $this->get('/register')->assertOk()->assertSee('action="'.route('register.store').'"', false)->assertSee('name="_token"', false);
        $this->get('/airline/login')->assertOk()->assertSee('action="'.route('airline.login.submit').'"', false)->assertSee('name="_token"', false);
    }

    public function test_customer_login_renders_general_and_field_level_credential_errors(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'unknown@example.test',
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Check the highlighted fields and try again.')
            ->assertSee('These credentials do not match our records.');
    }

    public function test_customer_login_shows_the_generic_error_for_another_role(): void
    {
        $superAdmin = $this->makeRoleUser('super_admin', 'wrong-role@example.test');

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $superAdmin->email,
                'password' => 'CorrectPassword123!',
            ])
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('These credentials do not match our records.');
        $this->assertGuest();
    }

    public function test_banned_customer_cannot_log_in_and_receives_the_suspension_message(): void
    {
        $this->addIsBannedColumn();
        $user = $this->makeRoleUser('customer', 'banned.login@example.test');
        $user->customer->forceFill(['is_banned' => true])->save();

        $this->withSession(['pre-login-marker' => 'discard-me'])
            ->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'CorrectPassword123!',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('pre-login-marker');

        $this->assertGuest();
        $this->get(route('login'))->assertSee(AuthService::CUSTOMER_SUSPENDED_MESSAGE);
    }

    public function test_banned_customer_with_an_active_session_is_logged_out_on_the_next_customer_request(): void
    {
        $this->addIsBannedColumn();
        $user = $this->makeRoleUser('customer', 'banned.session@example.test');
        $user->customer->forceFill(['is_banned' => true])->save();

        $this->actingAs($user)
            ->withSession(['pre-request-marker' => 'discard-me'])
            ->get(route('my.bookings'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('pre-request-marker');

        $this->assertGuest();
        $this->get(route('login'))->assertSee(AuthService::CUSTOMER_SUSPENDED_MESSAGE);
    }

    public function test_customer_login_works_when_the_optional_ban_column_is_absent(): void
    {
        $this->assertFalse(Schema::hasColumn('customers', 'is_banned'));
        $user = $this->makeRoleUser('customer', 'no-ban-column@example.test');
        $this->assertNull($user->customer->getAttribute('is_banned'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'CorrectPassword123!',
        ])->assertRedirect(route('my.bookings'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_register_renders_field_errors_old_values_and_never_old_passwords(): void
    {
        $payload = $this->validRegistration('register.error@example.test');
        $payload['name'] = '';
        $payload['password'] = 'weak-password';
        $payload['password_confirmation'] = 'different-password';

        $this->from(route('register'))
            ->post(route('register.store'), $payload)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['name', 'password']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Check the highlighted fields and try again.')
            ->assertSee('The name field is required.')
            ->assertSee('value="register.error@example.test"', false)
            ->assertDontSee('weak-password')
            ->assertDontSee('different-password');
    }

    public function test_admin_login_routes_remain_owned_by_admin_auth_controller(): void
    {
        $routes = Route::getRoutes();

        $this->assertSame(AdminAuthController::class.'@showLogin', $routes->getByName('admin.login')->getActionName());
        $this->assertSame(AdminAuthController::class.'@login', $routes->getByName('admin.login.submit')->getActionName());
    }

    public function test_registration_rolls_back_the_user_if_customer_creation_fails(): void
    {
        Customer::creating(static function (): void {
            throw new RuntimeException('Forced customer insert failure.');
        });

        $this->withoutExceptionHandling();

        try {
            $this->post('/register', $this->validRegistration('rollback@example.test'));
            $this->fail('The forced customer creation failure should bubble out of the transaction.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Forced customer insert failure.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('users', ['email' => 'rollback@example.test']);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_registration_reports_duplicate_email_and_weak_password_at_the_fields(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->post('/register', [
            'name' => 'Invalid Customer',
            'email' => 'taken@example.test',
            'password' => 'weak',
            'password_confirmation' => 'different',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors(['email', 'password']);
    }

    #[DataProvider('roleLoginCases')]
    public function test_login_pages_only_accept_their_own_profile(
        string $path,
        string $accountRole,
        bool $accepted,
        string $dashboard,
    ): void {
        $user = $this->makeRoleUser($accountRole, 'role.test@example.test');

        $response = $this->post($path, [
            'email' => $user->email,
            'password' => 'CorrectPassword123!',
        ]);

        if ($accepted) {
            $response->assertRedirect(route($dashboard));
            $this->assertAuthenticatedAs($user);

            return;
        }

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public static function roleLoginCases(): array
    {
        return [
            'customer accepts customer' => ['/login', 'customer', true, 'my.bookings'],
            'customer rejects airline admin' => ['/login', 'airline_admin', false, 'my.bookings'],
            'customer rejects super admin' => ['/login', 'super_admin', false, 'my.bookings'],
            'airline accepts airline admin' => ['/airline/login', 'airline_admin', true, 'airline.dashboard'],
            'airline rejects customer' => ['/airline/login', 'customer', false, 'airline.dashboard'],
            'airline rejects super admin' => ['/airline/login', 'super_admin', false, 'airline.dashboard'],
        ];
    }

    public function test_logout_logs_out_invalidates_the_session_and_regenerates_its_token(): void
    {
        $user = $this->makeRoleUser('customer', 'logout@example.test');
        $oldToken = session()->token();

        $this->actingAs($user)
            ->withSession(['logout-marker' => 'remove-me'])
            ->post('/logout')
            ->assertRedirect(route('home'))
            ->assertSessionMissing('logout-marker');

        $this->assertGuest();
        $this->assertNotSame($oldToken, session()->token());
    }

    public function test_guests_are_redirected_to_the_login_for_each_role_area(): void
    {
        $this->get('/my/bookings')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/airline')->assertRedirect(route('airline.login'));
    }

    public function test_login_rate_limit_is_shared_across_role_entry_points(): void
    {
        $credentials = ['email' => 'throttled@example.test', 'password' => 'incorrect'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', $credentials)->assertRedirect();
        }

        $this->post('/airline/login', $credentials)->assertTooManyRequests();
    }

    #[DataProvider('wrongRoleAreaCases')]
    public function test_wrong_role_gets_forbidden_for_protected_areas(string $role, string $path): void
    {
        $user = $this->makeRoleUser($role, 'wrong.area@example.test');

        $this->actingAs($user)->get($path)->assertForbidden();
    }

    public static function wrongRoleAreaCases(): array
    {
        return [
            'customer denied admin' => ['customer', '/admin'],
            'customer denied airline' => ['customer', '/airline'],
            'super admin denied customer' => ['super_admin', '/my/bookings'],
            'super admin denied airline' => ['super_admin', '/airline'],
            'airline admin denied customer' => ['airline_admin', '/my/bookings'],
            'airline admin denied super admin' => ['airline_admin', '/admin'],
        ];
    }

    public function test_user_without_a_profile_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'profileless@example.test',
            'password' => 'CorrectPassword123!',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'CorrectPassword123!',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_with_multiple_profiles_cannot_log_in(): void
    {
        $user = $this->makeRoleUser('customer', 'multiple.profiles@example.test');
        $user->superAdmin()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'CorrectPassword123!',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function validRegistration(string $email = 'new.customer@example.test'): array
    {
        return [
            'name' => 'New Customer',
            'email' => $email,
            'phone' => '+962 6 555 0100',
            'password' => 'CorrectPassword123!',
            'password_confirmation' => 'CorrectPassword123!',
        ];
    }

    private function makeRoleUser(string $role, string $email): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => 'CorrectPassword123!',
        ]);

        if ($role === 'customer') {
            $user->customer()->create(['credit_balance' => '0.00']);
        } elseif ($role === 'super_admin') {
            $user->superAdmin()->create();
        } elseif ($role === 'airline_admin') {
            $airline = Airline::create([
                'name' => 'Test Airline',
                'code' => 'TST',
                'country' => 'Jordan',
                'status' => 'active',
            ]);
            $user->airlineAdmin()->create(['airline_id' => $airline->id]);
        } else {
            throw new RuntimeException("Unsupported test role: {$role}");
        }

        return $user;
    }

    private function addIsBannedColumn(): void
    {
        if (! Schema::hasColumn('customers', 'is_banned')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->boolean('is_banned')->default(false);
            });
        }
    }
}
