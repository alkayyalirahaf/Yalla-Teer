@php($arabic = app()->isLocale('ar'))
<form method="POST" action="{{ route('home.language', $arabic ? 'en' : 'ar') }}" data-language-switch>
    @csrf
    <button class="preference-toggle" type="submit" role="switch" aria-checked="{{ $arabic ? 'true' : 'false' }}" aria-label="{{ __('Language') }} · العربية" title="{{ $arabic ? 'English' : 'العربية' }}">
        <span class="toggle-track" aria-hidden="true"><span></span></span><span>{{ $arabic ? 'عربي' : 'EN' }}</span>
    </button>
</form>
