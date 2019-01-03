# Asynchronous SOAP client

[![Build Status](https://travis-ci.org/wearesho-team/async-soap-guzzle.svg?branch=master)](https://travis-ci.org/wearesho-team/async-soap-guzzle)
[![codecov.io](https://codecov.io/github/wearesho-team/async-soap-guzzle/coverage.svg?branch=master)](https://codecov.io/github/wearesho-team/async-soap-guzzle?branch=master)

An asynchronous SOAP client build on top of Guzzle. The `SoapClient` implements [meng-tian/php-async-soap](https://github.com/meng-tian/php-async-soap).

## Installation
```
composer require wearesho-team/async-soap-guzzle
```

## Usage
```php
<?php

use GuzzleHttp\Client;
use Wearesho\AsyncSoap\Guzzle\Factory;

$factory = new Factory();
$client = $factory->create(new Client(), 'http://www.webservicex.net/Statistics.asmx?WSDL');

// async call
$promise = $client->callAsync('GetStatistics', [['X' => [1,2,3]]]);
$result = $promise->wait();

// sync call
$result = $client->call('GetStatistics', [['X' => [1,2,3]]]);

// magic method
$promise = $client->GetStatistics(['X' => [1,2,3]]);
$result = $promise->wait();
```

## Requirements
- PHP ^7.1
- ext-xml
- ext-soap

## Contributors
- [Meng Tian](mailto:tianmeng94@hotmail.com)
- [Roman Varkuta](mailto:roman.varkuta@gmail.com)

## License
This library is released under [MIT](./LICENSE) license.
