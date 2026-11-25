<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\City;
use App\Models\Country;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class CityController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [static function (Request $request, Closure $next) {
            if (!$request->user()) {
                return redirect()->guest(route('admin.login'));
            }

            abort_unless($request->user()->superAdmin()->exists(), 403);

            return $next($request);
        }];
    }

    public function index()
    {
        $cities = City::with('country')->withCount('airports')->orderBy('name')->orderBy('id')->paginate(5);

        return view('super-admin.cities.index', compact('cities'));
    }

    public function create()
    {
        return $this->form(new City);
    }

    public function store(Request $request)
    {
        City::create($this->validated($request));

        return redirect()->route('admin.cities')->with('status', 'City added.');
    }

    public function edit(City $city)
    {
        return $this->form($city);
    }

    public function update(Request $request, City $city)
    {
        $city->update($this->validated($request));

        return redirect()->route('admin.cities')->with('status', 'City updated.');
    }

    public function destroy(City $city)
    {
        try {
            $city->delete();
        } catch (QueryException $exception) {
            if (!$city->airports()->exists()) {
                throw $exception;
            }

            return back()->withErrors(['city' => 'Remove or reassign the linked airports before deleting this city.']);
        }

        return redirect()->route('admin.cities')->with('status', 'City deleted.');
    }

    private function form(City $city)
    {
        $countries = Country::orderBy('name')->pluck('name', 'id')->all();

        return view('super-admin.cities.form', compact('city', 'countries'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:100'],
            'best_season' => ['nullable', 'string', 'max:100'],
            'airport_transport' => ['nullable', 'string', 'max:10000'],
            'tips' => ['nullable', 'string', 'max:10000'],
        ]);
    }
}
