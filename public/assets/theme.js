(() => {
    const key = 'yallateer-theme';
    const system = matchMedia('(prefers-color-scheme: dark)');
    let preference = 'system';
    function apply(value) {
        preference = ['light', 'dark'].includes(value) ? value : 'system';
        const theme = preference === 'system' ? (system.matches ? 'dark' : 'light') : preference;
        document.documentElement.dataset.theme = theme;
        document.documentElement.dataset.bsTheme = theme;
        document.querySelectorAll('[data-theme-toggle]').forEach(button => button.setAttribute('aria-checked', String(theme === 'dark')));
        document.querySelectorAll('[data-theme-choice]').forEach(button => {
            button.setAttribute('aria-pressed', String(button.dataset.themeChoice === preference));
        });
    }
    try { preference = localStorage.getItem(key); } catch {}
    apply(preference);
    document.addEventListener('DOMContentLoaded', () => apply(preference));
    system.addEventListener('change', () => apply(preference));
    window.addEventListener('storage', event => { if (event.key === key || event.key === null) apply(event.newValue); });
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-theme-choice], [data-theme-toggle]');
        if (!button) return;
        apply(button.dataset.themeChoice || (document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark'));
        try { localStorage.setItem(key, preference); } catch {}
    });
})();
