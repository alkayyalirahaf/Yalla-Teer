@extends('super-admin.countries.layout')

@section('countries-content')
    @php
        $currencyNames = ['JOD' => 'Jordanian Dinar', 'AED' => 'UAE Dirham', 'EGP' => 'Egyptian Pound', 'TRY' => 'Turkish Lira', 'SAR' => 'Saudi Riyal', 'QAR' => 'Qatari Riyal', 'GBP' => 'British Pound', 'EUR' => 'Euro', 'THB' => 'Thai Baht', 'JPY' => 'Japanese Yen', 'USD' => 'US Dollar'];
        $currencyLabel = fn ($code) => $code ? ($code.(isset($currencyNames[$code]) ? ' - '.$currencyNames[$code] : '')) : '—';
    @endphp
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h1 class="mb-0">Destinations</h1>
        <a class="btn btn-primary px-4 py-2" href="{{ route('admin.countries.create') }}">Add destination</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr><th>Destination</th><th>Code</th><th>Currency</th><th>Language</th><th>Local apps</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($countries as $country)
                    <tr>
                        <td>{{ $country->name }}</td>
                        <td>{{ $country->code }}</td>
                        <td>{{ $currencyLabel($country->currency) }}</td>
                        <td>{{ $country->language }}</td>
                        <td class="text-break">{{ $country->local_apps ?: '—' }}</td>
                        <td class="text-nowrap">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.countries.edit', $country) }}">Edit</a>
                                @if($country->cities_count === 0)
                                    <form class="d-inline" method="POST" action="{{ route('admin.countries.destroy', $country) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @else
                                    <span class="text-muted">Has cities; cannot delete</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No destinations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $countries->links('pagination::bootstrap-5') }}
@endsection
