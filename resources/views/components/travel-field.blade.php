@props(['name', 'label', 'type' => 'text', 'value' => null, 'options' => null, 'required' => false])
@php
    $today = now('Asia/Amman')->toDateString();
    if (in_array($type, ['date', 'datetime-local']) && !str_contains($name, 'date_of_birth')) {
        $attributes = $attributes->merge(['min' => $type === 'date' ? $today : now('Asia/Amman')->format('Y-m-d\TH:i')]);
    } elseif ($type === 'date' && str_contains($name, 'date_of_birth')) {
        $attributes = $attributes->merge(['max' => $today]);
    }
@endphp
<label class="field">
    <span>{{ __($label) }}</span>
    @if($options !== null)
        <select name="{{ $name }}" @required($required) {{ $attributes }}>
            @foreach($options as $key => $description)
                <option value="{{ $key }}" @selected((string) old($name, $value) === (string) $key)>{{ __($description) }}</option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea name="{{ $name }}" @required($required) {{ $attributes }}>{{ old($name, $value) }}</textarea>
    @else
        <input name="{{ $name }}" type="{{ $type }}" @unless($type === 'password' || $type === 'file') value="{{ old($name, $value) }}" @endunless @required($required) {{ $attributes }}>
    @endif
    @if(isset($errors) && $errors->has($name))<small class="error">{{ $errors->first($name) }}</small>@endif
</label>
