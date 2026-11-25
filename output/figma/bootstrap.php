<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>__DIR__.'/preview.sqlite','session.driver'=>'file','cache.default'=>'array','mail.default'=>'log','app.url'=>'http://127.0.0.1:8139']);
Illuminate\Support\Facades\DB::purge();
return $app;
