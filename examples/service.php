<?php
use InoHibp\Service;

require __DIR__ . '/../vendor/autoload.php';

if (! isset($_SERVER['argv'][1])) {
    printf("Usage: %s <email>\n", $_SERVER['argv'][0]);
    exit();
}

$email = $_SERVER['argv'][1];

$service = new Service(array(
    'use_ssl' => true
));

try {
    $result = $service->checkEmail($email);
} catch (\Exception $e) {
    printf("Error: [%s] %s\n%s\n", get_class($e), $e->getMessage(), $e->getTraceAsString());
    exit();
}

if (null === $result) {
    printf("Not pwned\n");
} else {
    printf("Pwned on these websites: %s\n", implode(', ', $result));
}