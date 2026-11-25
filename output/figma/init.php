<?php
touch(__DIR__.'/preview.sqlite');
$app = require __DIR__.'/bootstrap.php';
Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
echo Illuminate\Support\Facades\Artisan::output();
Illuminate\Support\Facades\Artisan::call('db:seed', ['--class'=>'Database\\Seeders\\DemoSeeder','--force'=>true]);
echo Illuminate\Support\Facades\Artisan::output();
