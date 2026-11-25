@props(['name'])
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('search') <circle cx="10.5" cy="10.5" r="6"/><path d="m15 15 5 5"/> @break
        @case('plane') <path d="m22 2-7 20-4-9-9-4 20-7M11 13l5-5"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('seat') <path d="m5 4 3 10h10a2 2 0 0 1 2 2v2H7L3 5m6 13v3m8-3v3"/> @break
        @case('card') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18m-14 5h3"/> @break
        @case('ticket') <path d="M3 7h18v3a2 2 0 0 0 0 4v3H3v-3a2 2 0 0 0 0-4zm12 0v2m0 2v2m0 2v2"/> @break
    @case('light') <path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5 M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0"/> @break
        @case('dark') <path d="M20.5 14A9 9 0 0 1 10 3.5 9 9 0 1 0 20.5 14Z"/> @break
        @case('system') <path d="M3 4h18v13H3z M8 21h8m-4-4v4"/> @break
        @case('Dashboard') <path d="M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z"/> @break
        @case('Customers') <path d="M15 8a3 3 0 1 1-6 0 3 3 0 1 1 6 0 M5 21v-2a7 7 0 0 1 14 0v2"/> @break
        @case('Countries') <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0 M3 12h18 M12 3q-8 9 0 18 8-9 0-18"/> @break
        @case('Airports') <path d="M2 20h20 M12 16V3 M6 12l6-4 6 4 M9 17l3-2 3 2"/> @break
        @case('Reviews') <path d="m12 3 2.8 5.8 6.4.9-4.6 4.5 1.1 6.3-5.7-3-5.7 3 1.1-6.3-4.6-4.5 6.4-.9z"/> @break
        @case('Messages') <path d="M3 5h18v14H3z M3 6l9 7 9-7"/> @break
        @case('Notifications') <path d="M5 17h14l-2-3V9a5 5 0 0 0-10 0v5z M10 20q2 2 4 0"/> @break
        @case('AI Conversations') <path d="M3 4h18v13H9l-6 4z M7 9h10m-10 4h7"/> @break
        @case('Create flight') <path d="M12 4v16M4 12h16"/> @break
        @case('menu') <path d="M3 6h18M3 12h18M3 18h18"/> @break
    @endswitch
</svg>
