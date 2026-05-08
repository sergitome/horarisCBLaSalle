<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$urls = [
 'https://esb.optimalwayconsulting.com/fbib/1/jR4rgA5K6Chhh5vyfrxo9wTScdg2NT7K/FCBQWeb/fitxaClub/120',
 'https://esb.optimalwayconsulting.com/fbib/1/jR4rgA5K6Chhh5vyfrxo9wTScdg2NT7K/Match/getByTeamAllSeason/9382',
 'https://esb.optimalwayconsulting.com/fbib/1/jR4rgA5K6Chhh5vyfrxo9wTScdg2NT7K/Match/getByTeamAndMonth/9382/1',
 'https://esb.optimalwayconsulting.com/fbib/1/jR4rgA5K6Chhh5vyfrxo9wTScdg2NT7K/Match/getMatchClubMonth/120/1',
];
foreach ($urls as $url) {
  echo "URL: $url\n";
  try {
    $resp = Http::withHeaders([
      'Accept' => 'application/json, text/plain, */*',
      'Origin' => 'https://www.fbib.es',
      'Referer' => 'https://www.fbib.es/',
      'User-Agent' => 'Mozilla/5.0',
    ])->timeout(20)->get($url);
    echo 'Status: '.$resp->status()."\n";
    $body = $resp->body();
    echo substr($body, 0, 800)."\n----\n";
  } catch (Throwable $e) {
    echo 'ERR: '.$e->getMessage()."\n----\n";
  }
}
