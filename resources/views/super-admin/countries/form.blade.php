@csrf
@php
    $currencyOptions = [
        'JOD' => 'JOD - Jordanian Dinar',
        'AED' => 'AED - UAE Dirham',
        'SAR' => 'SAR - Saudi Riyal',
        'QAR' => 'QAR - Qatari Riyal',
        'EGP' => 'EGP - Egyptian Pound',
        'TRY' => 'TRY - Turkish Lira',
        'GBP' => 'GBP - British Pound',
        'EUR' => 'EUR - Euro',
        'USD' => 'USD - US Dollar',
        'THB' => 'THB - Thai Baht',
        'JPY' => 'JPY - Japanese Yen',
    ];
    $selectedCurrency = old('currency', $country->currency ?? '');
    if ($selectedCurrency && ! array_key_exists($selectedCurrency, $currencyOptions)) {
        $currencyOptions = [$selectedCurrency => $selectedCurrency.' - Currency'] + $currencyOptions;
    }
@endphp
<div class="card border">
    <div class="card-body">
        <h2 class="h5 mb-1">Destination details</h2>
        <p class="text-muted mb-3">Destination country, currency, language, and local apps. A destination has cities and airports.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Destination country</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                       value="{{ old('name', $country->name) }}" maxlength="100" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="code">Two-letter country code</label>
                <input class="form-control @error('code') is-invalid @enderror" id="code" name="code"
                       value="{{ old('code', $country->code) }}" maxlength="2" minlength="2" pattern="[A-Za-z]{2}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="currency">Currency (code and name)</label>
                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                    <option value="">Select currency</option>
                    @foreach($currencyOptions as $code => $label)
                        <option value="{{ $code }}" @selected((string) $selectedCurrency === (string) $code)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="language">Language</label>
                <input class="form-control @error('language') is-invalid @enderror" id="language" name="language"
                       value="{{ old('language', $country->language) }}" maxlength="60" required>
                @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="local_apps">Local apps (optional)</label>
                <textarea class="form-control @error('local_apps') is-invalid @enderror" id="local_apps" name="local_apps" rows="3" maxlength="10000">{{ old('local_apps', $country->local_apps) }}</textarea>
                @error('local_apps')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Save destination</button>
    <a class="btn btn-secondary" href="{{ route('admin.countries') }}">Cancel</a>
</div>
