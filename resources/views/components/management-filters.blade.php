@php($active = collect(request()->except('page'))->filter(fn ($value) => filled($value))->count())
<details class="management-filters" open data-management-filters data-active="{{ $active }}">
    <summary>{{ __('Search and filters') }} @if($active)<span>{{ $active }} {{ __('active') }}</span>@endif</summary>
    {{ $slot }}
</details>
