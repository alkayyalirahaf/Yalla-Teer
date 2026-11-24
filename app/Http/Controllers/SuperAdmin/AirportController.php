<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreAirportRequest;
use App\Http\Requests\SuperAdmin\UpdateAirportRequest;
use App\Models\Airport;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AirportController
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';
        $cityId = filter_var($request->query('city_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        $airports = Airport::query()
            ->with('city')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($cityId !== false, fn ($query) => $query->where('city_id', $cityId))
            ->orderBy('name')
            ->orderBy('code')
            ->paginate(5)
            ->withQueryString();

        return view('admin.airports.index', [
            'title' => 'Airports',
            'airports' => $airports,
            'cities' => City::query()->orderBy('name')->get(['id', 'name']),
            'filters' => ['search' => $search, 'city_id' => $cityId === false ? '' : $cityId],
        ]);
    }

    public function create(): View
    {
        return view('admin.airports.form', [
            'title' => 'Create Airport',
            'airport' => new Airport,
            'cities' => City::query()->orderBy('name')->get(['id', 'name']),
            'formMode' => 'create',
        ]);
    }

    public function store(StoreAirportRequest $request): RedirectResponse
    {
        Airport::create($request->validated());

        return redirect()->route('admin.airports')->with('status', 'Airport created.');
    }

    public function edit(Airport $airport): View
    {
        return view('admin.airports.form', [
            'title' => 'Edit Airport',
            'airport' => $airport->load('city'),
            'cities' => City::query()->orderBy('name')->get(['id', 'name']),
            'formMode' => 'edit',
        ]);
    }

    public function update(UpdateAirportRequest $request, Airport $airport): RedirectResponse
    {
        $airport->update($request->validated());

        return redirect()->route('admin.airports')->with('status', 'Airport updated.');
    }

    public function destroy(Airport $airport): RedirectResponse
    {
        if ($airport->departingFlights()->exists() || $airport->arrivingFlights()->exists()) {
            return back()->with('status', 'This airport cannot be deleted while flights use it as an origin or destination.');
        }

        $airport->delete();

        return redirect()->route('admin.airports')->with('status', 'Airport deleted.');
    }
}
