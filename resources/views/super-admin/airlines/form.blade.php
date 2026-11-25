@csrf
@foreach (['name' => ['Airline name', 150], 'code' => ['Code (max 3 characters)', 3], 'country' => ['Country', 100]] as $field => [$label, $max])
    <div class="mb-3">
        <label class="form-label" for="{{ $field }}">{{ __($label) }}</label>
        <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}"
               value="{{ old($field, $airline->$field ?? '') }}" maxlength="{{ $max }}" required>
        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endforeach
<div class="mb-3">
    <label class="form-label" for="status">{{ __('Status') }}</label>
    <select class="form-select" id="status" name="status" required>
        @foreach (['pending', 'active', 'suspended'] as $status)
            <option value="{{ $status }}" @selected(old('status', $airline->status ?? 'pending') === $status)>{{ __(ucfirst($status)) }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="logo">{{ __('Logo (optional, max 2 MB)') }}</label>
    <input class="form-control" id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp">
    @if ($airline->logo)<img class="mt-2" src="{{ asset('storage/'.$airline->logo) }}" alt="Current airline logo" width="80">@endif
</div>
<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit">{{ __('Save airline') }}</button>
    <a class="btn btn-secondary" href="{{ route('admin.airlines') }}">{{ __('Cancel') }}</a>
</div>
