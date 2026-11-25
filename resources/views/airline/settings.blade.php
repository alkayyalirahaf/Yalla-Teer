@extends('airline-admin.flights.layout')

@section('title', 'Airline Settings | Yalla Teer')

@section('flight-content')
<div class="stack" style="gap: 1.5rem;">
    <header>
        <h1>Airline settings</h1>
        <p class="subtle">Update your airline origin country and logo. Changes apply to your airline profile.</p>
    </header>

    @if(session('success'))
        <div class="panel" role="status" style="border-color:#86efac; background:#f0fdf4;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="panel" role="alert" style="border-color:#fecaca; background:#fef2f2;">
            <ul style="margin:0; padding-left:1.1rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('airline.settings.update') }}" enctype="multipart/form-data" class="panel stack" style="max-width: 760px;">
        @csrf
        @method('PUT')

        <section class="stack">
            <h2>Airline profile</h2>
            <div class="form-grid">
                <label class="field">
                    <span>Airline</span>
                    <input type="text" value="{{ $airline->name }}{{ $airline->code ? ' ('.$airline->code.')' : '' }}" readonly aria-readonly="true">
                    <small class="muted">Locked to your airline account.</small>
                </label>

                <label class="field" for="country">
                    <span>Origin country</span>
                    <select id="country" name="country" required>
                        <option value="">Select a destination country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" @selected(old('country', $airline->country) === $country->name)>
                                {{ $country->name }}{{ $country->code ? ' ('.$country->code.')' : '' }}
                            </option>
                        @endforeach
                        @if(old('country', $airline->country) && $countries->where('name', old('country', $airline->country))->isEmpty())
                            <option value="{{ old('country', $airline->country) }}" selected>{{ old('country', $airline->country) }} (current)</option>
                        @endif
                    </select>
                    @error('country')<small class="error">{{ $message }}</small>@enderror
                    <small class="muted">Choose the airline's origin country from existing destinations.</small>
                </label>
            </div>
        </section>

        <section class="stack">
            <h2>Airline logo</h2>
            <div class="form-grid">
                <div class="field">
                    <span>Current logo</span>
                    <div style="display:flex; align-items:center; gap:0.75rem; min-height:56px;">
                        @if($airline->logo)
                            <img src="{{ asset('storage/'.$airline->logo) }}" alt="{{ $airline->name }} logo" width="56" height="56" style="object-fit:contain; border-radius:0.5rem; background:#fff; border:1px solid #e5e7eb;">
                            <span class="subtle">A new upload will replace this logo.</span>
                        @else
                            <span class="subtle" style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border:1px dashed #d1d5db; border-radius:0.5rem; font-size:0.75rem;">No logo</span>
                            <span class="subtle">No airline logo uploaded yet.</span>
                        @endif
                    </div>
                </div>

                <label class="field" for="logo">
                    <span>Upload new logo (optional)</span>
                    <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                    @error('logo')<small class="error">{{ $message }}</small>@enderror
                    <small class="muted">JPG, PNG, or WEBP. Max 2 MB.</small>
                </label>
            </div>
        </section>

        <div class="actions">
            <button type="submit" class="button">Save settings</button>
            <a class="button secondary" href="{{ route('airline.dashboard') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
