WS-Addressing extension
=======================

This extension adds support for [WS-Addressing](https://www.w3.org/Submission/ws-addressing/) to the
SOAP client. It reads the WSDL file to determine if the server requires WS-Addressing. If so, it adds
the appropriate SOAP headers to every request. If the server does not support WS-Addressing them this
extension does nothing, so it is safe to always use it.


Setup
-----

The WS-addressing extension needs to parse the WSDL file. Therefor you should add it to the SOAP client
using the configuration options, not using the `__addSubscriber` method.

```php
$client = new SoapClient('http://example.org/wsa-server.wsdl', [
    'event_subscribers' => [
        new WSAddressing(),
    ],
]);
```

The `WSAddressing` constructor takes two optional parameters:

```
WSAddressing::__construct([$address[, $force]])
```

### `$address`

The address you want to use as a `ReplyTo` address. If omitted, it defaults to the anonymous address
http://schemas.xmlsoap.org/ws/2004/08/addressing/role/anonymous

### `$force`

Setting this parameter to `true` forces generation of WS-Addressing headers, even if the server WSDL
does not support it.


Headers
-------

The WS-Addressing extension adds the following headers to your SOAP requests:

### `MessageID`

A randomly generated UUID version 4

### `To`

The SOAP server remote address

### `Action`

The SOAP action

### `ReplyTo`

The address where the response should be sent. This header is only added for SOAP requests that expect a response.
One-way SOAP requests will not have a `ReplyTo` header.

### `From`

The source endpoint address where the message originated from. This header is optional and omitted by default, but can be included by using the `setIncludeFrom` method.
When included `setFromAddress` can be used to specify the from address.
```
$wsAddressing->setIncludeFrom(true);
$wsAddressing->setFromAddress('http://example.org/source');
```
When included but the value of fromAddress is omitted, it defaults to the anonymous address
http://schemas.xmlsoap.org/ws/2004/08/addressing/role/anonymous