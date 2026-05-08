<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$club = App\Models\ImportClub::where('fbib_club_id', 120)->firstOrFail();
$user = App\Models\User::where('username', 'admin')->first();
$service = app(App\Services\Imports\FbibImportService::class);

$execution = $service->importTeams($club, $user);
echo 'TEAMS_RESULT=' . $execution->result . PHP_EOL;
echo 'TEAMS_DETAILS=' . json_encode($execution->details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
echo 'COUNTS=' . json_encode(['created' => $execution->created_count, 'updated' => $execution->updated_count, 'skipped' => $execution->skipped_count, 'errors' => $execution->error_count]) . PHP_EOL;
