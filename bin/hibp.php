#!/usr/bin/env php
<?php
use InoHibp\Console\Application;
use InoHibp\Console\Command\Check;
require __DIR__ . '/../vendor/autoload.php';

$application = new Application();
$application->add(new Check());
$application->run();
