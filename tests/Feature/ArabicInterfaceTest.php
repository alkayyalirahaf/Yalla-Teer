<?php

namespace Tests\Feature;

use App\Services\AiService;
use App\Services\FlightSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\RouteFixtures;
use Tests\TestCase;

class ArabicInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_switch_returns_to_current_page_and_persists(): void
    {
        $this->from('/login')->post('/language/ar')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('dir="rtl"', false)->assertSee('أهلاً بعودتك');
        $this->get('/booking-lookup')->assertOk()->assertSee('الاستعلام عن الحجز');
        $this->from('/login')->post('/language/en')->assertRedirect('/login');
        $this->get('/login')->assertSee('dir="ltr"', false)->assertSee('Welcome back');
    }

    public function test_staff_dashboards_render_the_same_preference_switches_in_arabic(): void
    {
        $fixtures = RouteFixtures::create();
        $this->withSession(['landing_locale' => 'ar']);
        foreach (['admin' => '/admin', 'airlineUser' => '/airline'] as $role => $path) {
            $this->actingAs($fixtures[$role])->get($path)->assertOk()
                ->assertSee('dir="rtl"', false)->assertSee('data-language-switch', false)
                ->assertSee('data-theme-toggle', false)->assertSee('تسجيل الخروج');
        }
    }

    public function test_arabic_city_and_airport_names_resolve_to_existing_filters(): void
    {
        RouteFixtures::create();
        $filters = app(FlightSearchService::class)->validateFilters([
            'from' => 'عمان', 'to' => 'مطار الملك حسين الدولي', 'depart' => now()->addDay()->toDateString(),
        ]);
        $this->assertSame('AQJ', $filters['to']);
        $this->assertDatabaseHas('cities', ['name' => 'Amman', 'id' => $filters['origin_city_id']]);
    }

    public function test_gateway_tips_follow_message_language_independently_of_ui(): void
    {
        config(['services.gemini.key' => '', 'services.dev3_ai.endpoint' => 'https://example.test/tips', 'services.dev3_ai.key' => 'test']);
        Http::fake(['example.test/*' => Http::response(['tip_keys' => ['verify_itinerary']])]);
        app()->setLocale('en');
        $this->assertSame([__(AiService::TIPS['verify_itinerary'], [], 'ar')], app(AiService::class)->reply('tips', 'كيف أسافر؟'));
        app()->setLocale('ar');
        $this->assertSame([AiService::TIPS['verify_itinerary']], app(AiService::class)->reply('tips', 'How should I prepare?'));
    }
}
