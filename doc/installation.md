Installation
============

This extension can be installed using Composer. Tell composer to install the extension:

```bash
$ composer require prezent/soap-client
```

## Usage

The Prezent SoapClient extends PHP's built-in SoapClient. You can therefor use it as a drop-in replacement.
There are a few extra configuration options and methods.

## Configuration

The Prezent SoapClient supports a few extra configuration options on top of [PHP's built-in SoapClient options](http://php.net/manual/en/soapclient.soapclient.php):

### event\_dispatcher

Optional. A custom `\Symfony\Component\EventDispatcher\EventDispatcherInterface` that will be used by the client. If you do not
pass this option, a new `\Symfony\Component\EventDispatcher\EventDispatcher` will be created.

### event\_listeners

An array of event listeners. Each listener is itself an array of an event name, a callable and optionally a priority. Example:

```php
'event_listeners' => [
    [Events::REQUEST, 'myListenerFunction', 10],
]
```

### event\_subscribers

An array of event subscribers implementing `Symfony\Component\EventDispatcher\EventSubscriberInterface`. Example:

```php
'event_subscribers' => [
    new WSAddressing(),
]
```

## New methods

The Prezent SoapClient has a few extra methods, but all the methods have been prefixed with
double underscores like PHP's built-in methods and should not clash with any of your SOAP methods.

### `__addListener(string $eventName, callable $callable[, int $priority])`

Add a new event listener to the client. Note that the `WSDL_REQUEST` and `WSDL_RESPONSE` events are triggered during client
construction. If you want to listen to those events, you must add your listener through the client configuration options,
not by calling this method.

### `__addSubscriber(Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)`

Add a new event subscriber to the client. Note that the `WSDL_REQUEST` and `WSDL_RESPONSE` events are triggered during client
construction. If you want your subscriber to listen to those events, you must add your subscriber through the client
configuration options, not by calling this method.
