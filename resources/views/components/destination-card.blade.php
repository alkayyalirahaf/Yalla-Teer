@props(['place', 'localImage' => false, 'translateLabels' => false, 'currency' => null])

@php
    $image = $localImage ? 'assets/landing/catalog/' . strtolower($place['code']) . '.webp' : ($place['image'] ?? null);
    if ($image && str_starts_with($image, 'assets/')) {
        $image = asset($image);
    }
    $currencyNames = ['JOD' => 'Jordanian Dinar', 'AED' => 'UAE Dirham', 'EGP' => 'Egyptian Pound', 'TRY' => 'Turkish Lira', 'SAR' => 'Saudi Riyal', 'QAR' => 'Qatari Riyal', 'GBP' => 'British Pound', 'EUR' => 'Euro', 'THB' => 'Thai Baht', 'JPY' => 'Japanese Yen', 'USD' => 'US Dollar'];
    $currencyValue = $currency ?? ($place['currency'] ?? null);
    $currencyLabel = $currencyValue ? ($currencyValue.(isset($currencyNames[$currencyValue]) ? ' - '.$currencyNames[$currencyValue] : '')) : null;
@endphp

<article @class(['destination-card', 'destination-card-text' => !$image])>
    @if($image)
        <img src="{{ $image }}" data-destination-photo alt="" loading="lazy" decoding="async" width="640" height="425">
    @endif
    <div class="destination-copy">
        <span class="destination-code">{{ $place['code'] }}</span>
        @if(isset($place['country']))<p>{{ $translateLabels ? __($place['country']) : $place['country'] }}</p>@endif
        <h3>{{ $translateLabels ? __($place['name']) : $place['name'] }}</h3>
        @if($currencyLabel)
            <p class="muted"><strong>{{ $translateLabels ? __('Currency') : 'Currency' }}:</strong> {{ $currencyLabel }}</p>
        @endif
    </div>
</article>
