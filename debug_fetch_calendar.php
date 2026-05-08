<?php
require 'vendor/autoload.php';
$client = new GuzzleHttp\Client(['timeout' => 30, 'headers' => ['User-Agent' => 'Mozilla/5.0']]);
$response = $client->get('https://www.fbib.es/equipo/9382/calendario');
echo 'STATUS=' . $response->getStatusCode() . PHP_EOL;
$html = (string) $response->getBody();
file_put_contents('storage/app/last_calendar_fetch.html', $html);
echo 'LEN=' . strlen($html) . PHP_EOL;
echo substr($html, 0, 2000) . PHP_EOL;
