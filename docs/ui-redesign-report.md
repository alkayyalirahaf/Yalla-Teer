# Yalla Teer frontend redesign

## Implementation

The shared application shell now offers Light, Dark and System preferences. A synchronous local script resolves the stored choice before the styles load. It follows OS changes in System mode and synchronizes open tabs using storage events. The switch exposes each choice as a named button with a pressed state. Customer, airline staff, platform staff and ticket views use the same preference.

The homepage pairs the booking tools with the travel hero on wide screens and stacks them on smaller screens. The destination rail uses consistently sized photographs and places its directory action alongside its heading. Flight results group fare filters and sorting with the original search form. Reset keeps the underlying itinerary. The account pages group booking actions with the page title and credit balance with the wallet heading. Payment separates the fare breakdown from the checkout action. Tickets use screen-specific theme rules and retain their existing print and export styles.

The assistant keeps its original conversation and navigation handlers. Its desktop layout places history beside the conversation. The floating jet uses the original PNG at its natural aspect ratio, with keyboard focus, reduced-motion support and position adjustments near other controls. The only attachment supplied was the written brief. The identity of the existing jet as the intended replacement image could not be confirmed.

The staff sidebars now use the existing outline SVG component for dashboards, flights, airports, countries, customer accounts, bookings, payments, reviews, notifications, messages, conversations and the mobile menu. The Platform Admin dashboard groups recent booking and payment ledgers side by side when space permits. Management search forms share a collapsible filter container, show an active count and preserve the existing form actions. Staff actions, destructive controls and account links use shared button styles.

## Design tokens

| Role | Light | Dark |
| --- | --- | --- |
| brand | #bf6240 | #bf6240 |
| action | #a34d30 | #de9478 |
| hover | #873d25 | #eaa78f |
| link | #984428 | #de9478 |
| on-action | white | #201711 |
| tint | #f5e5dd | #382923 |
| secondary | #24343d | #24343d |
| bg | #f8f6f3 | #101418 |
| surface | #ffffff | #191f25 |
| raised | #f1eeea | #222a31 |
| text | #20252b | #f5f5f5 |
| muted | #606970 | #adb5bd |
| border | #d5cec7 | #424c55 |
| control-border | #85807b | #76818c |
| warning | #79500b | #edc57e |
| danger | #a23138 | #ffabb0 |
| success | #246647 | #8ed7ac |

Body copy uses the existing IBM Plex Sans stack. The redesign includes theme-aware foregrounds, surfaces, options, placeholders, input borders, focus indicators, statuses and interactive states. The primary action uses a darker terracotta in Light mode and a lighter terracotta in Dark mode. Staff tables and financial amounts use tabular numerals. Existing language direction is preserved and the new appearance labels have Arabic translations.

## Verification

- Vite production build passed. Vite deferred resolution of the directory fallback image to runtime. The image exists in public/assets/landing/wing.webp.
- Laravel compiled the Blade views successfully. The final full suite reported 169 passing tests, eight failures and 1,858 assertions.
- Headless Chrome checked the homepage in both themes at 320, 390, 768, 1024, 1366 and 1920 pixels. All four travel-tool tabs were present and the page stayed within the viewport at each size.
- Separate browser checks exercised customer, airline and platform fixture logins, account pages, flight details, assistant history, pending-booking payment, dashboard and management pages, and sample CRUD form views. The sampled staff and account pages stayed within the viewport in mobile Dark mode and desktop Light mode. Browser checks covered rendering and navigation. They did not submit every CRUD form.
- Confirmed System mode responds to emulated OS changes in both directions, Dark mode persists across reload, and inactive staff filters collapse on mobile and reopen on activation.
- The browser exception listener recorded no uncaught runtime exceptions during the sampled navigation. External font and capture scripts were blocked during these checks. This was not a complete network or console audit.

## Outstanding verification

The eight suite failures cover expected-role GET access, invalid mutation handling, passenger submission, passport-scan route protection, seat-hold duration, cancellation/refund behavior, authenticated login-page logout markup and payment conversion wording. The hold test expected ten minutes and observed fifteen. The payment test expected an exchange rate displayed as 1.41 and the rendered view used 1.41044. The suite was not run before the redesign, so these results do not establish when the failures began.

An attempted payment view for the completed fixture booking showed a Laravel error page. The pending fixture and no-booking payment views rendered. Live payment, refunds, live assistant replies, every intermediate seat/passenger state, ticket export, every RTL layout, every breakpoint and full WCAG AA conformance remain unverified. Some pages received shared layout and component changes only. The work does not establish individual visual sign-off for every page.

## Source file inventory

Paths are relative to the Laravel project. The list covers files changed or added for this redesign. The report itself is docs/ui-redesign-report.md. The production build also regenerated public/build/manifest.json and its hashed assets. Existing working-tree edits to assistant backend files and the assistant page were present before this work.

- lang/ar.json
- public/assets/assistant-shortcut.css
- public/assets/theme-switch.css
- public/assets/theme-tokens.css
- public/assets/theme.js
- resources/css/app.css
- resources/css/landing.css
- resources/js/app.js
- resources/views/admin/airports/index.blade.php
- resources/views/airline-admin/flights/layout.blade.php
- resources/views/airline/bookings/index.blade.php
- resources/views/airline/bookings/show.blade.php
- resources/views/airline/dashboard.blade.php
- resources/views/airline/flights/create.blade.php
- resources/views/airline/flights/edit.blade.php
- resources/views/airline/flights/index.blade.php
- resources/views/booking/lookup.blade.php
- resources/views/booking/payment.blade.php
- resources/views/booking/profile.blade.php
- resources/views/booking/records.blade.php
- resources/views/booking/ticket.blade.php
- resources/views/components/landing-icon.blade.php
- resources/views/components/management-filters.blade.php
- resources/views/components/theme-switch.blade.php
- resources/views/customer/credits.blade.php
- resources/views/flights/index.blade.php
- resources/views/layouts/app.blade.php
- resources/views/super-admin/ai-conversations/index.blade.php
- resources/views/super-admin/airline-admins/index.blade.php
- resources/views/super-admin/airlines/index.blade.php
- resources/views/super-admin/credits/content.blade.php
- resources/views/super-admin/customers/index.blade.php
- resources/views/super-admin/dashboard/content.blade.php
- resources/views/super-admin/flights/index.blade.php
- resources/views/super-admin/management-layout.blade.php
- resources/views/super-admin/messages/index.blade.php
- resources/views/super-admin/notifications/index.blade.php
- resources/views/super-admin/payments/index.blade.php
- resources/views/super-admin/reviews/index.blade.php
- resources/views/welcome.blade.php
