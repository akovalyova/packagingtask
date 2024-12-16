<?php

use App\Application;
use App\Service\CacheService;
use App\Service\ExtractorService;
use App\Service\BinPackingService;
use App\Service\ValidatorService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

$entityManger = require __DIR__ . '/src/bootstrap.php';

$httpClient = new Client();
$cacheService =  new CacheService($entityManger);
$apiService = new BinPackingService($entityManger, $httpClient);
$validatorService = new ValidatorService();
$extractorService = new ExtractorService();

if (!isset($argv[1])) {
    echo "No input json file specified.\n";
    exit;
}

$request = new Request('POST', new Uri('http://localhost/pack'), ['Content-Type' => 'application/json'], $argv[1]);

try {
    $application = new Application($cacheService, $validatorService, $extractorService, $apiService);
    $response = $application->run($request);

    echo "<<< In:\n" . Message::toString($request) . "\n\n";
    echo ">>> Out:\n" . Message::toString($response) . "\n\n";
} catch (Exception $exception) {
    echo "Exception occurred:\n" . $exception->getMessage() . "\n\n";
}
