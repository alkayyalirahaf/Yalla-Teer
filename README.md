# Yalla Teer

Yalla Teer is a flight reservation platform built with Laravel 12, Blade, Tailwind CSS, and MySQL. Travelers can search and compare flights, enter passenger details, choose seats, and complete checkout. Airline staff manage flights and seat inventory, and platform administrators oversee airlines, bookings, and payments.

## Local setup

Use PHP 8.2 or later with `pdo_mysql`, Composer, Node.js with npm, and a running MySQL server. If you use XAMPP, start MySQL from its control panel.

1. From the project folder, install dependencies and create `.env` if it does not already exist. The copy command below uses PowerShell.

   ```powershell
   composer install
   if (-not (Test-Path .env)) { Copy-Item .env.example .env }
   npm ci
   ```

2. Create a local MySQL database named `yallaTeer` and update `.env` with your local credentials. Set `DB_PASSWORD` to your MySQL password if your account uses one.

   ```dotenv
   APP_URL=http://127.0.0.1:8011
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=yallaTeer
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. If you just created `.env`, run `php artisan key:generate`. Then migrate and seed the database, build assets, and start Laravel.

   ```powershell
   php artisan config:clear
   php artisan migrate --seed
   npm run build
   php artisan serve --port=8011
   ```

   Open [Yalla Teer locally](http://127.0.0.1:8011/).

## Demo accounts

The local demo accounts share the password `FlyNow-Demo-2026!`.

| Role | Email |
| --- | --- |
| Traveler | `traveler@demo.example.test` |
| Royal Jordanian staff | `staff.rj@demo.example.test` |
| Emirates staff | `staff.ek@demo.example.test` |
| Turkish Airlines staff | `staff.tk@demo.example.test` |
| Platform administrator | `admin@demo.example.test` |

## Booking demo

1. Sign in as the demo traveler and search for **Amman (AMM) → Dubai (DXB)**. On a freshly seeded database, choose tomorrow for departure and four days after departure for the default return. For a one-way booking, choose **One Way** and leave the return date blank.
2. Select Royal Jordanian flight `RJD000O` in **Economy**. For a round trip, select return flight `RJD000R` in **Economy**. The seeder may use an earlier matching return already in the database for its sample booking.
3. Enter the traveler and booking contact details, then continue to **Select seats**. The demo booking marks `1A`–`1E` on the outbound and `1A`–`1C` on its selected return as unavailable in a fresh seed. Choose an available seat for each traveler on each leg. The selected seats are held for 15 minutes after you continue.
4. Review the itinerary and fare. With Stripe test credentials configured, continue to checkout and complete a test payment to reach the booking confirmation. To see a confirmed itinerary with seeded tickets, open [demo booking DEMO0500](http://127.0.0.1:8011/my/bookings/DEMO0500) as the demo traveler.

For an existing local database, apply demo fixtures with `php artisan db:seed --class=DemoSeeder`. Re-running on the same date keeps existing bookings. Find the exact flights used by the sample round trip in booking `DEMO0500`.

## Integrations and tests

- Stripe checkout uses `STRIPE_KEY`, `STRIPE_SECRET`, and `STRIPE_WEBHOOK_SECRET`. Use test credentials for the local demo. The webhook endpoint is `/stripe/webhook`.
- Gemini powers the optional travel assistant. See the [assistant setup](docs/gemini-assistant.md).
- Run the test suite with `php artisan test`, or run `php artisan test --filter=DemoSeederTest` to check the demo fixtures. The test configuration uses in-memory SQLite and requires `pdo_sqlite`.

## Project references

- [Entity relationship diagram](https://dbdiagram.io/d/6ace73bde308025ea5e4d931)
- [Local demo data reference](docs/demo-data.md)
