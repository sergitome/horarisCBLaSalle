<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
DB::table('jobs')->truncate();
DB::table('failed_jobs')->truncate();
App\Models\ImportExecution::query()->whereIn('result', ['queued', 'running'])->update(['result' => 'error', 'finished_at' => now()]);
echo 'cleanup-ok' . PHP_EOL;
