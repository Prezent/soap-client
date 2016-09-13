Prezent SoapClient
==================

The Prezent SoapClient is an extension to PHP's built-in SoapClient that brings easy
extensability through an event dispatcher and built-in support for WS-Addressing.

Installation
------------

```
$ composer require prezent/soap-client
```

Example usage
-------------

```php
use Prezent\Soap\Client\SoapClient;
use Prezent\Soap\Client\Extension\WSAddressing;

$client = new SoapClient('http://example.org/wsa-server.wsdl', [
    'event_subscribers' => [
        new WSAddressing('http://example.org/return-address'),
    ],
]);

$client->someMethod('arg');
```


Documentation
-------------

The complete documentation can be found in the [doc directory](doc/index.md).
