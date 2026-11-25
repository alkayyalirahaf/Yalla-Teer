<div class="theme-switch preference-pair">
    <x-landing-language />
    <button class="preference-toggle" type="button" data-theme-toggle role="switch" aria-label="{{ __('Dark mode') }}" title="{{ __('Dark mode') }}">
        <span class="toggle-track" aria-hidden="true"><span></span></span>
        <span class="theme-sun"><x-landing-icon name="light" /></span><span class="theme-moon"><x-landing-icon name="dark" /></span>
    </button>
    <button class="system-theme" type="button" data-theme-choice="system" title="{{ __('System default') }}" aria-label="{{ __('System default') }}"><x-landing-icon name="system" /></button>
</div>
