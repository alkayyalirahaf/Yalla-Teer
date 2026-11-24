@csrf
@foreach(['name' => ['Name', 100], 'code' => ['Two-letter country code', 2], 'currency' => ['Currency', 10], 'language' => ['Language', 60]] as $field => [$label, $length])
    <div class="mb-3">
        <label class="form-label" for="{{ $field }}">{{ __($label) }}</label>
        <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}"
               value="{{ old($field, $country->$field) }}" maxlength="{{ $length }}" required
               @if($field === 'code') minlength="2" pattern="[A-Za-z]{2}" @endif>
        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endforeach
<div class="mb-3">
    <label class="form-label" for="local_apps">{{ __('Local apps (optional)') }}</label>
    <textarea class="form-control @error('local_apps') is-invalid @enderror" id="local_apps" name="local_apps" rows="3" maxlength="10000">{{ old('local_apps', $country->local_apps) }}</textarea>
    @error('local_apps')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit">{{ __('Save country') }}</button>
    <a class="btn btn-secondary" href="{{ route('admin.countries') }}">{{ __('Cancel') }}</a>
</div>
