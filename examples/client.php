<?php
use InoHibp\Client;
require __DIR__ . '/../vendor/autoload.php';

if (! isset($_SERVER['argv'][1])) {
    printf("Usage: %s <email>\n", $_SERVER['argv'][0]);
    exit();
}

$email = $_SERVER['argv'][1];
$endpointUrl = 'http://haveibeenpwned.com/api/breachedaccount/';
$httpClient = new \Zend\Http\Client();

$client = new Client($endpointUrl, $httpClient);

try {
    $response = $client->checkEmail($email);
} catch (\Exception $e) {
    printf("Error: [%s] %s\n%s\n", get_class($e), $e->getMessage(), $e->getTraceAsString());
    exit();
}

if (null === $response) {
    printf("Not pwned\n");
} else {
    printf("Pwned on these websites: %s\n", implode(', ', $response));
}
