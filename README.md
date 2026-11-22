# Yalla Teer

**Chosen project option:** An airline travel booking and management platform.

Yalla Teer is a Laravel web application for discovering and comparing flights, booking travel, and managing airline operations. It includes customer, airline administrator, and super administrator workflows.

## Features

- Search available flights and compare fares, schedules, baggage allowances, and seats.
- Customer registration, sign in, traveler profiles, and booking history.
- Multi-step booking flow with passenger details, seat selection, booking lookup, and ticket views.
- Stripe Checkout integration for booking payments (requires Stripe credentials and webhook configuration).
- **AI travel assistant powered by Google Gemini:** answer travel questions, find or compare flights, and provide travel tips. The assistant uses flight results from the application and does not make bookings or payments.
- **AI passport data extraction:** help fill traveler details from a passport image when Gemini is configured; users should review extracted information before using it.
- Airline administration for flights, bookings, and reviews.
- Super Admin tools for the dashboard, airlines, airline administrators, customers, countries, cities, airports, flights, bookings, payments, credits, reviews, AI conversations, notification logs, and contact messages.
- Review moderation, customer credit history, contact messages, and notification logs.
- Responsive Blade interface with Yalla Teer branding.

## Requirements

- PHP 8.2 or later and the PHP extensions required by Laravel 12.
- Composer.
- SQLite (the default local database) or another Laravel-supported database.
- Node.js and npm to build the frontend assets.
- A Google Gemini API key to enable Gemini features. The application can run without it, but the Gemini assistant and passport extraction will be unavailable.

## Setup

Clone the repository and enter the project directory. Then install the PHP dependencies:

```bash
composer install
```

Create your environment file. On macOS/Linux/Git Bash:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### Configure the environment

The default `.env.example` uses SQLite. Create the SQLite database file if it does not exist:

```bash
touch database/database.sqlite
```

On Windows PowerShell:

```powershell
New-Item -ItemType File -Force database/database.sqlite
```

To use MySQL or PostgreSQL instead, update the `DB_*` values in `.env` and create the database before migrating.

To enable the Gemini assistant and passport extraction:

1. Create an API key in [Google AI Studio](https://aistudio.google.com/app/apikey).
2. Add the key to `.env`:

   ```dotenv
   GEMINI_API_KEY=your-google-gemini-api-key
   GEMINI_MODEL=gemini-3.1-flash-lite
   ```

Keep the key server-side in `.env`. Do not add a `VITE_` prefix, commit the key, or expose it in browser code. After changing configuration, run `php artisan config:clear`.

For Stripe payments, set `STRIPE_KEY`, `STRIPE_SECRET`, and `STRIPE_WEBHOOK_SECRET` in `.env` and configure a Stripe webhook for the application's webhook endpoint.

### Generate the application key, migrate, and seed

```bash
php artisan key:generate
php artisan migrate --seed
```

In the local environment, the database seeder adds demo data for development. Demo account emails and the shared demo password are documented in [`docs/demo-data.md`](docs/demo-data.md). Do not use demo credentials in production. Seeded records are fictional test fixtures.

### Build frontend assets

```bash
npm install
npm run build
```

During frontend development, use `npm run dev` in a separate terminal instead of the production build.

### Start the application

```bash
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000).

## Useful routes

- `/` — home page
- `/flights` — flight search
- `/compare` — compare selected flights
- `/assistant` — AI travel assistant (customer sign-in required)
- `/my/bookings` — customer bookings
- `/admin/login` — Super Admin sign-in
- `/admin` — Super Admin dashboard
- `/admin/airlines` — airline management
- `/airline/login` — airline administrator sign-in

## Repository and submission notes

The repository's `.gitignore` excludes local dependencies, environment secrets, and generated assets, including `vendor/`, `node_modules/`, `.env`, and `public/build/`. Install dependencies and build assets using the commands above. Never submit a populated `.env` file or API keys.

# Trello Link [https://trello.com/b/GwcxKa78/yalla-%D9%86%D8%B7%D9%8A%D8%B1]
