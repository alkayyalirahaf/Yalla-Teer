<?php

return [
    'customer' => [
        'name' => env('AUTH_SEED_CUSTOMER_NAME', 'Yalla Teer Customer'),
        'email' => env('AUTH_SEED_CUSTOMER_EMAIL', 'customer@example.test'),
        'password' => env('AUTH_SEED_CUSTOMER_PASSWORD'),
    ],
    'super_admin' => [
        'name' => env('AUTH_SEED_SUPER_ADMIN_NAME', 'Yalla Teer Platform Admin'),
        'email' => env('AUTH_SEED_SUPER_ADMIN_EMAIL', 'super-admin@example.test'),
        'password' => env('AUTH_SEED_SUPER_ADMIN_PASSWORD'),
    ],
    'airline_admin' => [
        'name' => env('AUTH_SEED_AIRLINE_ADMIN_NAME', 'Yalla Teer Airline Admin'),
        'email' => env('AUTH_SEED_AIRLINE_ADMIN_EMAIL', 'airline-admin@example.test'),
        'password' => env('AUTH_SEED_AIRLINE_ADMIN_PASSWORD'),
    ],
    'airline' => [
        'name' => env('AUTH_SEED_AIRLINE_NAME', 'Yalla Teer Demo Airline'),
        'code' => env('AUTH_SEED_AIRLINE_CODE', 'YTD'),
        'country' => env('AUTH_SEED_AIRLINE_COUNTRY', 'Jordan'),
    ],
];
