<?php
$app=require __DIR__.'/bootstrap.php';
$c=App\Models\AiConversation::findOrFail(1);
$path='dev3-ai/'.$c->customer_id.'/00000000-0000-4000-8000-000000000001.json';
Illuminate\Support\Facades\Storage::disk('local')->put($path,json_encode(['message'=>'Help me plan a trip to Dubai.','tips'=>'Compare departure times and baggage allowances. Direct flights take approximately three hours.','flights'=>[]]));
$c->update(['file_path'=>$path]);
App\Models\Review::where('customer_id',1)->delete();
echo 'Prepared isolated conversation and review fixtures.';
