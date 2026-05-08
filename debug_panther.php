<?php
require 'vendor/autoload.php';
$_SERVER['PANTHER_CHROME_BINARY'] = 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe';
$client = Symfony\Component\Panther\Client::createChromeClient('C:\\laragon\\www\\cblasalle\\drivers\\chromedriver.exe', ['--headless', '--disable-gpu', '--window-size=1400,1400']);
$client->request('GET', 'https://www.fbib.es/equipo/9382/calendario');
$client->waitForVisibility('body');
echo substr($client->getPageSource(), 0, 4000) . PHP_EOL;
