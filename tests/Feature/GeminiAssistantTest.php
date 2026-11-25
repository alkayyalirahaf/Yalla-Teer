<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeminiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'test-gemini-key', 'services.gemini.model' => 'gemini-3.1-flash-lite']);
        Http::preventStrayRequests();
        Storage::fake('local');
    }

    private function response(array $tips): array
    {
        return ['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode(['tips' => $tips], JSON_UNESCAPED_UNICODE)]]]]]];
    }

    public function test_existing_assistant_form_saves_and_renders_gemini_reply_for_its_owner(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        $user->customer()->create(['credit_balance' => 0]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->response(['يمكنك زيارة دبي في الشتاء.']))]);

        $this->actingAs($user)->get('/assistant')->assertOk()->assertSee('Send message');
        $this->post('/my/assistant', ['mode' => 'tips', 'message' => 'متى أزور دبي؟'])->assertRedirect();
        $conversation = AiConversation::firstOrFail();
        $this->get(route('my.assistant.show', $conversation))->assertOk()->assertSee('يمكنك زيارة دبي في الشتاء.');
        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && ! $request->hasHeader('Authorization')
            && ! str_contains($request->url(), 'test-gemini-key')
            && str_contains($request['contents'][0]['parts'][0]['text'], 'متى أزور دبي؟')
            && ! str_contains($request['contents'][0]['parts'][0]['text'], $user->email));

        /** @var User $other */
        $other = User::factory()->createOne();
        $other->customer()->create(['credit_balance' => 0]);
        $this->actingAs($other)->get(route('my.assistant.show', $conversation))->assertNotFound();
    }

    public function test_assistant_find_ignores_conflicting_fields_from_an_older_form(): void
    {
        $user = User::factory()->createOne();
        $user->customer()->create(['credit_balance' => 0]);
        $country = DB::table('countries')->insertGetId([
            'name' => 'Jordan', 'code' => 'JO', 'currency' => 'JOD', 'language' => 'Arabic',
        ]);
        $origin = DB::table('cities')->insertGetId(['name' => 'Amman', 'country_id' => $country]);
        $destination = DB::table('cities')->insertGetId(['name' => 'Aqaba', 'country_id' => $country]);
        DB::table('airports')->insert([
            ['city_id' => $origin, 'code' => 'AMM', 'name' => 'Amman'],
            ['city_id' => $destination, 'code' => 'AQJ', 'name' => 'Aqaba'],
        ]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->response(['Try another date.']))]);

        $this->actingAs($user)->post('/my/assistant', [
            'mode' => 'find', 'message' => 'Find a flight', 'trip' => 'one_way',
            'from' => 'AMM', 'origin_city_id' => $origin,
            'to' => 'AQJ', 'destination_city_id' => $destination,
            'depart' => now('Asia/Amman')->addDay()->toDateString(),
            'return' => now('Asia/Amman')->addDays(2)->toDateString(),
        ])->assertSessionHasNoErrors()->assertRedirect();

        Http::assertSentCount(1);
    }

    public function test_gemini_receives_only_explicit_database_flight_context(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->response(['Compare the recorded baggage allowances.']))]);
        $flights = [['id' => 7, 'number' => 'RJ101', 'cabins' => [['class' => 'economy', 'price' => '150.00']]]];
        (new AiService)->reply('compare', 'Compare these flights', $flights);
        Http::assertSent(fn ($request) => json_decode($request['contents'][0]['parts'][0]['text'], true)['database_flights'] === $flights);
    }

    public function test_quota_error_does_not_expose_provider_body_or_key(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => 'test-gemini-key'], 429)]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Gemini usage limit reached.');
        (new AiService)->reply('tips', 'Hello');
    }

    public function test_blocked_or_malformed_responses_are_not_saved(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        $user->customer()->create(['credit_balance' => 0]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']])]);
        $this->actingAs($user)->from('/assistant')->post('/my/assistant', ['mode' => 'tips', 'message' => 'Hello'])->assertRedirect('/assistant')->assertSessionHasErrors('assistant');
        $this->assertDatabaseCount('ai_conversations', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_missing_key_disables_gemini_without_sending_requests(): void
    {
        config(['services.gemini.key' => '', 'services.dev3_ai' => []]);
        $this->assertFalse((new AiService)->available());
        Http::assertNothingSent();
    }
}
