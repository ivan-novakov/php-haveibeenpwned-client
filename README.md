# PHP Client for haveibeenpwned.com

## Introduction

The [haveibeenpwned.com site](http://haveibeenpwned.com/) is a project by [Troy Hunt](http://www.troyhunt.com/) which allows you to check, if your account has been compromised in a recent password disclosure by submitting an email address. You can find more info about the site in the [corresponding blogpost](http://www.troyhunt.com/2013/12/introducing-have-i-been-pwned.html).

The site also provides [a RESTful remote API](http://haveibeenpwned.com/Api). This library is a simple PHP client implementation, which can interact with that API. It is both a client library and a client CLI - you can use it in your project or you can use the provided CLI.

## Features

* lightweight and easily extendable
* SSL support with peer verification
* unit tested
* ready to use CLI

## Dependencies

* **PHP** >= 5.3.4
* **zendframework/zend-http** - the ZF2 HTTP client implementation
* **zendframework/zend-json** - the ZF2 JSON encoder/decoder implementation
* **symfony/console** - the Console component from the Symfony object, used to implement the CLI
* **cURL** - for SSL support

## Installation

The easiest way to install the library is through composer. 

To use it as a standalone CLI:

```
$ php composer.phar create-project ivan-novakov/php-haveibeenpwned-client ivan-novakov/php-haveibeenpwned-client ~1
```

To add it to your project:

```
$ php composer.phar require ivan-novakov/php-haveibeenpwned-client:~1
```

## Usage

Basic usage:

```php
use InoHibp\Service;

$service = new Service();
try {
    $result = $service->checkEmail($email);
} catch (\Exception $e) {
    // handle exception
}

if (null === $result) {
    printf("Not pwned\n");
} else {
    printf("Pwned on these websites: %s\n", implode(', ', $result));
}
```

The `checkEmail()` method returns either `null` if the email has not been pwned

By default a HTTP connection is used. To enforce a SSL connection you need to initialize the service with the `use_ssl` option:

```php
$service = new Service(array(
    'use_ssl' => true
));
```

The library uses the cURL PHP extension to handle SSL connections. It is configured to perform peer and host verification (`CURLOPT_SSL_VERIFYPEER = true`, `CURLOPT_SSL_VERIFYHOST = 2`). For more information on how to handle SSL connections in PHP (ZF2), see my blogpost [HTTPS connections with Zend Framework 2](http://blog.debug.cz/2012/11/https-connections-with-zend-framework-2.html).

The peer is verified against the CA root certificate of the [haveibeenpwned.com site](https://haveibeenpwned.com/). The CA root certificate is stored in `ssl/ca-bundle.pem`. If for some reason you need to change that, you can specify alternative path to the bundle:

```php
$service = new Service(array(
    'use_ssl' => true,
    'ca_file' => /alternative/path/ca-bundle.pem
));
```

