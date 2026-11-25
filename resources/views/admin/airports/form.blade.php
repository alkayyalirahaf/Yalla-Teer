@extends('super-admin.management-layout')

@section('admin-title', $title)

@section('admin-content')
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.airports') }}">{{ __('← Back to airports') }}</a>
<h1>{{ __($title) }}</h1>

<form action="{{ $formMode === 'create' ? route('admin.airports.store') : route('admin.airports.update', $airport) }}" method="POST" class="panel p-3 p-md-4 stack">
    @csrf
    @if($formMode === 'edit')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label" for="name">{{ __('Airport name') }}</label>
        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $airport->name) }}" maxlength="150" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="code">{{ __('IATA code') }}</label>
        <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $airport->code) }}" minlength="3" maxlength="3" pattern="[A-Za-z]{3}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="city_id">{{ __('City') }}</label>
        <select class="form-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id" required>
            <option value="">{{ __('Choose a city') }}</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}" @selected((string) old('city_id', $airport->city_id) === (string) $city->id)>{{ $city->name }}</option>
            @endforeach
        </select>
        @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">{{ $formMode === 'create' ? 'Create airport' : 'Save changes' }}</button>
        <a class="btn btn-secondary" href="{{ route('admin.airports') }}">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
