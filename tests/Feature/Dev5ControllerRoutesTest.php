<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicSite\HomeController;
use App\Models\Airport;
use App\Models\City;
use App\Models\ContactMessage;
use App\Models\Country;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Dev5ControllerRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_uses_the_home_controller_and_keeps_the_welcome_view(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertViewIs('welcome');

        $this->assertSame(HomeController::class.'@index', Route::getRoutes()->getByName('home')->getActionName());
    }

    public function test_contact_form_posts_through_http_and_saves_a_new_message(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('<h1>Contact and Suggestions</h1>', false)
            ->assertSee('action="'.route('contact.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertDontSee('data-preview-form', false)
            ->assertDontSee('Preview only.');

        $this->post(route('contact.store'), [
            'name' => '  Guest Traveler  ',
            'email' => ' GUEST@EXAMPLE.TEST ',
            'type' => 'question',
            'message' => 'Please contact me.',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Guest Traveler',
            'email' => 'guest@example.test',
            'type' => 'question',
            'status' => 'new',
            'customer_id' => null,
        ]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Thank you. Your message has been sent.');
    }

    public function test_contact_form_http_validation_returns_field_errors_and_old_input(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Guest Traveler',
                'email' => 'not-an-email',
                'type' => 'invalid',
                'message' => '',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors(['email', 'type', 'message'])
            ->assertSessionHasInput('name', 'Guest Traveler');

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('value="Guest Traveler"', false)
            ->assertSee('Enter a valid email address.');
    }

    public function test_contact_post_route_is_throttled(): void
    {
        $payload = [
            'name' => 'Guest Traveler',
            'email' => 'guest@example.test',
            'type' => 'question',
            'message' => 'A question.',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('contact'))
                ->post(route('contact.store'), $payload)
                ->assertRedirect(route('contact'));
        }

        $this->from(route('contact'))
            ->post(route('contact.store'), $payload)
            ->assertTooManyRequests();
        $this->assertSame(5, ContactMessage::count());
    }

    public function test_privacy_page_explains_booking_payment_contact_ai_and_credit_data(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertViewIs('pages.information')
            ->assertSee('passport and identity details')
            ->assertSee('Card details are entered on Stripe Checkout.')
            ->assertSee('We never store your card details.')
            ->assertSee('Messages sent through our contact form are saved')
            ->assertSee('AI features may process text you enter')
            ->assertSee('Passport photos are never stored.')
            ->assertSee('kept in a transaction ledger.');
    }

    public function test_missing_page_uses_the_friendly_custom_404(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Check the address or search for a flight.')
            ->assertSee('href="'.route('home').'"', false)
            ->assertDontSee('Stack trace')
            ->assertDontSee('Symfony\\Component');
    }

    public function test_forbidden_page_uses_the_friendly_custom_403(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->get(route('my.credits'))
            ->assertForbidden()
            ->assertSee('Access denied')
            ->assertSee('href="'.route('home').'"', false)
            ->assertDontSee('Symfony\\Component');
    }

    public function test_airport_crud_routes_work_over_http_for_super_admins(): void
    {
        $admin = $this->makeSuperAdmin();
        $city = $this->makeCity();
        $this->actingAs($admin);

        $this->get(route('admin.airports'))
            ->assertOk()
            ->assertViewIs('admin.airports.index');
        $this->get(route('admin.airports.create'))
            ->assertOk()
            ->assertViewIs('admin.airports.form')
            ->assertSee('name="_token"', false);

        $this->post(route('admin.airports.store'), [
            'code' => 'amm',
            'name' => 'Queen Alia International',
            'city_id' => $city->id,
        ])->assertRedirect(route('admin.airports'));

        $airport = Airport::where('code', 'AMM')->firstOrFail();

        $this->get(route('admin.airports.edit', $airport))
            ->assertOk()
            ->assertViewIs('admin.airports.form')
            ->assertSee('name="_method" value="PUT"', false);

        $this->put(route('admin.airports.update', $airport), [
            'code' => 'amm',
            'name' => 'Queen Alia Airport',
            'city_id' => $city->id,
        ])->assertRedirect(route('admin.airports'));

        $this->assertDatabaseHas('airports', [
            'id' => $airport->id,
            'code' => 'AMM',
            'name' => 'Queen Alia Airport',
        ]);

        $this->delete(route('admin.airports.destroy', $airport))
            ->assertRedirect(route('admin.airports'));
        $this->assertDatabaseMissing('airports', ['id' => $airport->id]);
    }

    public function test_airport_index_search_and_city_filter_are_served_over_http(): void
    {
        $admin = $this->makeSuperAdmin();
        $city = $this->makeCity('Amman');
        $otherCity = $this->makeCity('Aqaba');
        Airport::create(['city_id' => $city->id, 'code' => 'AMM', 'name' => 'Queen Alia International']);
        Airport::create(['city_id' => $otherCity->id, 'code' => 'AQJ', 'name' => 'King Hussein International']);

        $this->actingAs($admin)
            ->get(route('admin.airports', ['search' => 'AMM', 'city_id' => $city->id]))
            ->assertOk()
            ->assertSee('Queen Alia International')
            ->assertDontSee('King Hussein International');
    }

    public function test_airport_routes_reject_guests_and_non_super_admin_roles(): void
    {
        $this->get(route('admin.airports'))->assertRedirect(route('admin.login'));

        $customerUser = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'password' => 'password',
        ]);
        Customer::create(['user_id' => $customerUser->id, 'credit_balance' => '0.00']);

        $this->actingAs($customerUser)->get(route('admin.airports'))->assertForbidden();
    }

    public function test_airport_form_renders_field_level_validation_errors(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->from(route('admin.airports.create'))
            ->post(route('admin.airports.store'), [
                'code' => 'A1',
                'name' => '',
                'city_id' => 999999,
            ])
            ->assertRedirect(route('admin.airports.create'))
            ->assertSessionHasErrors(['code', 'name', 'city_id']);

        $this->get(route('admin.airports.create'))
            ->assertOk()
            ->assertSee('The airport code must contain exactly three letters.')
            ->assertSee('Enter the airport name.')
            ->assertSee('Select an existing city.');
    }

    public function test_credit_wallet_shows_a_friendly_empty_state(): void
    {
        $customerUser = User::create([
            'name' => 'Empty Wallet Owner',
            'email' => 'empty-wallet@example.test',
            'password' => 'password',
        ]);
        Customer::create(['user_id' => $customerUser->id, 'credit_balance' => '0.00']);

        $this->actingAs($customerUser)
            ->get(route('my.credits'))
            ->assertOk()
            ->assertViewIs('customer.credits')
            ->assertSee('No credit transactions yet.');
    }

    public function test_credit_wallet_requires_customer_role_and_renders_live_read_only_data(): void
    {
        $this->get(route('my.credits'))->assertRedirect(route('login'));

        $customerUser = User::create([
            'name' => 'Wallet Owner',
            'email' => 'wallet@example.test',
            'password' => 'password',
        ]);
        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'credit_balance' => '42.75',
        ]);

        foreach (range(0, 11) as $index) {
            CreditTransaction::forceCreate([
                'customer_id' => $customer->id,
                'type' => $index % 2 === 0 ? 'admin_adjust' : 'used',
                'amount' => '1.00',
                'balance_after' => '42.75',
                'note' => 'Transaction '.$index,
                'created_at' => now()->subDays($index),
                'updated_at' => now()->subDays($index),
            ]);
        }

        $this->actingAs($customerUser)
            ->get(route('my.credits'))
            ->assertOk()
            ->assertViewIs('customer.credits')
            ->assertSee('JOD 42.75')
            ->assertSee('Transaction 0')
            ->assertDontSee('Transaction 11')
            ->assertSee('Next');

        $this->assertSame('42.75', $customer->fresh()->credit_balance);
        $this->assertSame(12, CreditTransaction::count());

        $admin = $this->makeSuperAdmin();
        $this->actingAs($admin)->get(route('my.credits'))->assertForbidden();
    }

    private function makeSuperAdmin(): User
    {
        $user = User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        SuperAdmin::create(['user_id' => $user->id]);

        return $user;
    }

    private function makeCity(string $name = 'Amman'): City
    {
        $country = Country::firstOrCreate(
            ['code' => 'JO'],
            ['name' => 'Jordan', 'currency' => 'JOD', 'language' => 'Arabic'],
        );

        return City::create(['country_id' => $country->id, 'name' => $name]);
    }
}
