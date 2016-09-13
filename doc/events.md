Events
======

The Prezent SoapClient adds an event dispatcher to the soap client, allowing you to hook into every
step of the soap request/response flow.


Event flow
----------

There are two event flows in the SoapClient. One on client construction and one when a soap method is called.
The events hook into various parts of PHP's built-in SoapClient flow.

### Client construction flow

These events are dispatched during the client construction. If you want to listen to these events, you must add your
listeners and subscribers using the `event_listeners` or `event_subscribers` configuration options. You cannot add your
listeners using the `__addListener` or `__addSubscriber` methods.

1. A `WSDL_REQUEST` event is dispatched. If no listeners generate a response, then the
   WSDL will be loaded using the built-in `stream_context`.
2. A `WSDL_RESPONSE` event is dispatched with the contents of the WSDL file

### Method call flow

1. You call a SOAP method either using a magic [`__call`](http://php.net/manual/en/soapclient.call.php) method
   or using the [`__soapCall`](http://php.net/manual/en/soapclient.soapcall.php) method.
2. A `CALL` event is dispatched, containing the method name and arguments
3. PHP converts the methods and arguments to a request for it's built-in [`__doRequest`](http://php.net/manual/en/soapclient.dorequest.php) method.
4. A `REQUEST` event is dispatched. If no listeners generate a response, then the request will be executed using the
   built-in `stream_context`. Any uncaught exceptions are converted to `SoapFault`.
5. The response is converted to a `DOMDocument`. Non-SOAP responses trigger a `SoapFault`.
6. A `RESPONSE` event is dispatched. Any uncaught exceptions are converted to `SoapFault`.
7. PHP converts the response XML to an object, array or class depending on the SoapClient configuration.
8. A `FINISH` even is dispatched containing the converted result
9. The final response is retuned

### Faults

1. When a `SoapFault` is thrown, a `FAULT` event is dispatched.


Built-in event listeners
------------------------

There are two built-in event listeners that cannot be removed. These listeners will request the WSDL file or SOAP response
using the built-in `stream_context`. Both listeners have a priority of -999. To prevent these listeners from running, add your
own listeners at a higher priority that return a response and stop event propagation. See the `Events::WSDL_REQUEST` and
`Events::REQUEST` events below.

Events
------

The `Prezent\Soap\Client\Events` class defined constants for all events.

### `Events::WSDL_REQUEST` (or "wsdl.request")

The `WSDL_REQUEST` event is triggered during construction of the SOAP client to load the WSDL file.

Listeners of this event are passed a `Prezent\Soap\Client\Event\WsdlRequestEvent` instance. To implement your
own transport, Listeners can set the contents of the WSDL file as a string and stop propagation. Example:

```php
$client->__addListener(Events::WSDL_REQUEST, function (WsdlRequestEvent $event) {
    $response = $guzzle->get($event->getUri());
    $event->setWsdl($response->getBody());
    $event->stopPropagation();
});
```

### `Events::WSDL_RESPONSE` (or "wsdl.response")

The `WSDL_RESPONSE event` is triggered during construction of the SOAP client, after the WSDL file has
been loaded. Listeners can use this event to parse the WSDL file themselves, for example for features not
supported by the built-in SoapClient.

Listeners of this event are passen a `Prezent\Soap\Client\Event\WsdlResponseEvent`. Any changes make to the
WSDL `DOMDocument` are passed to the built-in SoapClient. Example:

```php
$client->__addListener(Events::WSDL_RESPONSE, function(WsdlResponseEvent $event) {
    // Fix bad WSDL
    $wsdl = $event->getWsdl();
});
```

### `Events::CALL` (or "soap.call")

The `CALL` event is triggered just after calling a SOAP method.

The listener is passed a `Prezent\Soap\Client\Event\CallEvent` instance. It allows changing the method
and arguments for e.g. custom marshalling. The final arguments should be a value that van be understood
by PHP's built-in SoapClient. Example:

```php
$client->__addListener(Events::CALL, function(CallEvent $event) {
    $arguments = $serializer->serialize($event->getArguments());
    $event->setArguments($arguments);
});
```

### `Events::REQUEST` (or "soap.request")

The `REQUEST` event is triggered when a SOAP request is about to be sent over a transport.

The listener is passed a `Prezent\Soap\Client\Event\RequestEvent` instance. The request and any
parameters may be modified here. When implementing a custom transport, you can set the response
as a string on the event and stop propagation. Optionally you can also set the request and response
headers to facilitate tracing.

All exceptions are converted to `SoapFault`.

Example:

```php
$client->__addListener(Events::REQUEST, function(RequestEvent $event) {
    $response = $guzzle->request('POST', $event->getLocation(), [
        'body' => $event->getRequest()->saveXML(),
    ]);

    $event->setResponse($response->getBody());
    $event->stopPropagation();
});
```

### `Events::RESPONSE` (or "soap.response")

The `RESPONSE` event is triggered when a SOAP response has been recieved. The listener is passed a
`Prezent\Soap\Client\Event\ResponseEvent` instance. The response can be modified before being processed
by the rest of the client.

All exceptions are converted to `SoapFault`.

Example:

```php
$client->__addListener(Events::RESPONSE, function(ResponseEvent $event) {
    $logger->log($event->getResponse()->saveXML());
});
```

### `Events::FINISH` (or "soap.finish")

The `FINISH` event is triggered just before returning from a SOAP method. Listeners are passed a
`Prezent\Soap\Client\Event\FinishEvent` instance. It allows changing the response for e.g. custom marshalling.

The initial response is a value, array or object as returned by PHP's built-in SoapClient. Example:

```php
$client->__addListener(Events::FINISH, function(FinishEvent $event) {
    $response = $serializer->unserialize(MyResponse::class, $event->getResponse());
    $event->setResponse($response);
});
```

### `Events::FAULT` (or "soap.fault")

The `FAULT` event is triggered when a SOAP call has thrown a SoapFault. Listeners are passed a
`Prezent\Soap\Client\Event\FaultEvent` instance.

If propagation is stopped on this event then the fault
will be considered as handled. Any response provided to the event will be returned. If propagation
is not stopped then the fault will be re-thrown. Example:

```php
$client->__addListener(Events::FAULT, function(FaultEvent $event) use ($backup) {
    $event->setResponse($backup->getResponse());
    $event->stopPropagation();
});
```
