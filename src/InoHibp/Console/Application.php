<?php

namespace InoHibp\Console;

use Symfony\Component\Console;


class Application extends Console\Application
{

    const NAME = 'Have I Been Pwned CLI';

    const VERSION = 'v1.0.1';


    public function __construct($name = null, $version = null)
    {
        if (null === $name) {
            $name = self::NAME;
        }
        
        if (null === $version) {
            $version = self::VERSION;
        }
        
        parent::__construct($name, $version);
    }
}