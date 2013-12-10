#!/usr/bin/env php
<?php
use Symfony\Component\Console\Application;
use InoHibp\Console\Command\Check;
require __DIR__ . '/../vendor/autoload.php';

$application = new Application('Have I Been Pwned CLI');
$application->add(new Check());
$application->run();
