<?php

namespace App\Http\Controllers\Customer;

use App\Models\AiConversation;
use App\Models\Airport;
use App\Models\City;
use App\Models\Recommendation;
use App\Services\AiService;
use App\Services\FlightSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AiConversationController
{
    public function index(Request $request, AiService $ai)
    {
        $customer = $request->user()?->customer;
        abort_unless($customer, 403);
        return view('pages.assistant', $this->chatData($customer, $ai));
    }

    private function chatData(\App\Models\Customer $customer, AiService $ai): array
    {
        $conversations = $customer->aiConversations()->latest('id')->paginate(15);
        $available = $ai->available();
        $airports = Airport::with('city')->orderBy('code')->get();
        $cities = City::orderBy('name')->get();
        $filters = [];

        return compact('conversations', 'available', 'airports', 'cities', 'filters');
    }

    public function store(Request $request, AiService $ai, FlightSearchService $search)
    {
        $customer = $request->user()?->customer;
        abort_unless($customer, 403);
        $data = $request->validate([
            'mode' => ['required', 'in:find,compare,tips'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if (! $ai->available()) {
            return back()->withInput()->withErrors(['assistant' => 'AI integration is unavailable because a provider has not been configured.']);
        }

        $flights = collect();
        if ($data['mode'] === 'find') {
            $input = $request->all();
            // Match the form when an older client submits conflicting controls.
            foreach (['from' => 'origin_city_id', 'to' => 'destination_city_id'] as $airport => $city) {
                if ($request->filled($airport)) {
                    unset($input[$city]);
                }
            }
            if (in_array($input['trip'] ?? null, ['one_way', 'one-way'], true)) {
                unset($input['return']);
            }

            $filters = $search->validateFilters($input);
            $flights = $search->search($filters)->getCollection();
        } elseif ($data['mode'] === 'compare') {
            if (is_array($request->input('flights'))) {
                $request->merge(['flights' => array_values(array_filter($request->input('flights'), fn ($id) => $id !== null && $id !== ''))]);
            }
            $ids = $request->validate([
                'flights' => ['required', 'array', 'min:2', 'max:3'],
                'flights.*' => ['required', 'integer', 'distinct', 'min:1'],
            ]);
            $flights = $search->availableFlights()->whereIn('id', $ids['flights'])
                ->with(['airline', 'originAirport', 'destinationAirport', 'flightClasses' => fn ($query) => $query
                    ->whereHas('seats', fn ($seats) => $seats->where('status', 'available'))
                    ->withCount(['seats as available_seats_count' => fn ($seats) => $seats->where('status', 'available')])])->get();

            if ($flights->count() !== count($ids['flights']) || $flights->contains(fn ($flight) => $flight->flightClasses->isEmpty())) {
                throw ValidationException::withMessages(['flights' => 'Select two or three available flights with published cabins and seats.']);
            }
        }

        $snapshot = $flights->map(fn ($flight) => [
            'id' => $flight->id, 'number' => $flight->flight_number, 'airline' => $flight->airline->name,
            'from' => $flight->originAirport->code, 'to' => $flight->destinationAirport->code,
            'departure' => $flight->departure_at->format('Y-m-d H:i'),
            'arrival' => $flight->arrival_at->format('Y-m-d H:i'),
            'cabins' => $flight->flightClasses->map(fn ($class) => [
                'id' => $class->id, 'class' => $class->class, 'price' => $class->price,
                'cabin_baggage_kg' => $class->cabin_baggage_kg, 'checked_baggage_kg' => $class->checked_baggage_kg,
                'available_seats' => $class->available_seats_count,
            ])->values()->all(),
        ])->values()->all();

        try {
            $tips = $ai->reply($data['mode'], $data['message'], $snapshot);
        } catch (\RuntimeException $exception) {
            return back()->withInput()->withErrors(['assistant' => $exception->getMessage()]);
        }

        $transcript = [
            'message' => $data['message'], 'tips' => $tips, 'flights' => $snapshot,
            'recorded_at' => now()->toIso8601String(), 'timezone' => config('app.timezone'),
        ];
        $path = 'dev3-ai/'.$customer->id.'/'.Str::uuid().'.json';

        try {
            if (! Storage::disk('local')->put($path, json_encode($transcript, JSON_THROW_ON_ERROR))) {
                throw new \RuntimeException('Conversation storage failed.');
            }

            $conversation = DB::transaction(function () use ($customer, $data, $path, $flights) {
                $conversation = AiConversation::create([
                    'customer_id' => $customer->id, 'type' => $data['mode'],
                    'title' => Str::limit($data['message'], 140, ''), 'file_path' => $path,
                ]);
                foreach ($flights as $flight) {
                    Recommendation::create([
                        'customer_id' => $customer->id, 'flight_id' => $flight->id,
                        'ai_conversation_id' => $conversation->id, 'source' => 'chatbot',
                        'reason' => 'Database result for the customer-submitted '.$data['mode'].' filters.',
                    ]);
                }

                return $conversation;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            return back()->withInput()->withErrors(['assistant' => 'The conversation could not be saved. Please try again.']);
        }

        return redirect()->route('my.assistant.show', $conversation->id);
    }

    public function show(Request $request, int $conversation, ?AiService $ai = null)
    {
        $customer = $request->user()?->customer;
        abort_unless($customer, 403);
        $conversation = $customer->aiConversations()->findOrFail($conversation);
        // Do not read arbitrary file paths from other conversation integrations.
        abort_unless(preg_match('#^dev3-ai/'.$customer->id.'/[a-f0-9-]{36}\.json$#', $conversation->file_path), 404);

        $contents = Storage::disk('local')->get($conversation->file_path);
        abort_unless(is_string($contents), 404);
        $transcript = json_decode($contents, true);
        abort_unless(is_array($transcript) && isset($transcript['message'], $transcript['tips'], $transcript['flights']), 404);

        return view('pages.assistant', $this->chatData($customer, $ai ?? app(AiService::class)) + [
            'activeConversation' => $conversation, 'transcript' => $transcript,
        ]);
    }
}
