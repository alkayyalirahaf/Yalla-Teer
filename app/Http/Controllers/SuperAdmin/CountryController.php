<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CountryController
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $countries = Country::withCount('cities')->orderBy('name')->paginate(5);

        return view('super-admin.countries.index', compact('countries'));
    }

    public function create(Request $request)
    {
        $this->authorizeAdmin($request);

        return view('super-admin.countries.create', ['country' => new Country]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        Country::create($this->validatedData($request));

        return redirect()->route('admin.countries')->with('status', 'Country created.');
    }

    public function edit(Request $request, Country $country)
    {
        $this->authorizeAdmin($request);

        return view('super-admin.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $this->authorizeAdmin($request);
        $country->update($this->validatedData($request, $country));

        return redirect()->route('admin.countries')->with('status', 'Country updated.');
    }

    public function destroy(Request $request, Country $country)
    {
        $this->authorizeAdmin($request);

        try {
            $deleted = DB::transaction(function () use ($country) {
                $country = Country::whereKey($country->id)->lockForUpdate()->firstOrFail();

                if ($country->cities()->exists()) {
                    return false;
                }

                $country->delete();

                return true;
            });
        } catch (QueryException $exception) {
            if (!in_array((int) ($exception->errorInfo[1] ?? 0), [1451, 547, 787], true)) {
                throw $exception;
            }

            $deleted = false;
        }

        if (!$deleted) {
            return redirect()->route('admin.countries')
                ->withErrors(['country' => 'This country has cities and cannot be deleted.']);
        }

        return redirect()->route('admin.countries')->with('status', 'Country deleted.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->superAdmin, 403);
    }

    private function validatedData(Request $request, ?Country $country = null): array
    {
        $request->merge([
            'name' => is_string($request->name) ? trim($request->name) : $request->name,
            'code' => is_string($request->code) ? strtoupper(trim($request->code)) : $request->code,
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('countries', 'name')->ignore($country)],
            'code' => ['required', 'string', 'regex:/^[A-Z]{2}$/', Rule::unique('countries', 'code')->ignore($country)],
            'currency' => ['required', 'string', 'max:10'],
            'language' => ['required', 'string', 'max:60'],
            'local_apps' => ['nullable', 'string', 'max:10000'],
        ]);
    }
}
