<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\ImportExecution::query()->latest('id')->take(6)->get() as $e) {
    echo implode('|', [$e->id, $e->type, $e->result, optional($e->started_at)?->toDateTimeString(), optional($e->finished_at)?->toDateTimeString(), json_encode($e->details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]) . PHP_EOL;
}
echo 'MATCHES=' . App\Models\ClubMatch::count() . PHP_EOL;
