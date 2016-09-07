<?php

namespace Prezent\Soap\Client;

use Prezent\Soap\Client\Event\RequestEvent;
use Prezent\Soap\Client\Event\ResponseEvent;
use Prezent\Soap\Client\Event\WsdlRequestEvent;
use Prezent\Soap\Client\Event\WsdlResponseEvent;
use SoapClient as BaseSoapClient;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Extendable SOAP client
 *
 * @author Sander Marechal
 */
class SoapClient extends BaseSoapClient
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool Toggle tracing. This cannot be named `trace` or it will conflict with an internal SoapClient variable
     */
    private $tracing = false;

    /**
     * {@inheritDoc}
     */
    public function __construct($wsdl, array $options = [])
    {
        // Setup stream context
        $this->streamContext = isset($options['stream_context'])
            ? $options['stream_context']
            : stream_context_create();

        // Check for tracing
        if (isset($options['trace'])) {
            $this->tracing = $options['trace'];
        }

        // Set up event dispatcher
        if (isset($options['event_dispatcher'])) {
            if (!($options['event_dispatcher'] instanceof EventDispatcherInterface)) {
                throw new \RuntimeException(
                    "The 'event_dispatcher' option must be a " . EventDispatcherInterface::class
                );
            }

            $this->eventDispatcher = $options['event_dispatcher'];
            unset($options['event_dispatcher']);
        } else {
            $this->eventDispatcher = new EventDispatcher();
        }

        // Attach listeners
        if (isset($options['event_listeners'])) {
            foreach ($options['event_listeners'] as $listener) {
                call_user_func_array([$this, '__addListener'], $listener);
            }

            unset($options['event_listeners']);
        }

        // Attach subscribers
        if (isset($options['event_subscribers'])) {
            foreach ($options['event_subscribers'] as $subscriber) {
                $this->__addSubscriber($subscriber);
            }

            unset($options['event_subscribers']);
        }

        // Fallback listener for loading WSDL files
        $this->__addListener(Events::WSDL_REQUEST, function (WsdlRequestEvent $event) {
            $event->setWsdl(file_get_contents($event->getUri(), false, $this->streamContext));
            $event->stopPropagation();
        }, -999);

        // Fallback listener for making SOAP requests
        $this->__addListener(Events::REQUEST, function (RequestEvent $event) {
            $response = parent::__doRequest(
                $event->getRequest()->saveXML(),
                $event->getLocation(),
                $event->getAction(),
                $event->getVersion(),
                (int) $event->isOneWay()
            );

            $dom = new \DOMDocument();

            if ($response) {
                $dom->loadXML($response);
            }

            $event->setResponse($dom);
            $event->stopPropagation();
        }, -999);

        // Load WSDL using a data:// URI. This allows us to load the WSDL by any transport
        // instead of always using the built-in method. It also allows custom WSDL parsing
        parent::SoapClient('data://text/xml;base64,' . base64_encode($this->getWsdl($wsdl)), $options);
    }

    /**
     * Add an event listener
     *
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function __addListener($eventName, $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Add an event subscriber
     *
     * @param EventSubscriberInterface $subscriber
     * @return void
     */
    public function __addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * {@inheritDoc}
     */
    public function __call($method, $args)
    {
        $result = parent::__call($method, $args);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function __soapCall($method, $args, $options = [], $inputHeaders = [], &$outputHeaders = [])
    {
        $result = parent::__soapCall($method, $args, $options, $inputHeaders, $outputHeaders);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($request);

        $requestEvent = new RequestEvent($dom, $location, $action, $version, $oneWay === 1);
        $response = $this->eventDispatcher->dispatch(Events::REQUEST, $requestEvent)->getResponse();

        if ($this->tracing) {
            $this->__last_request = $requestEvent->getRequest()->saveXML();
            $this->__last_request_headers = $requestEvent->getRequestHeaders();
        }

        if (!$response) {
            throw new \RuntimeException('Could not get response');
        }
        
        $responseEvent = new ResponseEvent($response);
        $this->eventDispatcher->dispatch(Events::RESPONSE, $responseEvent);

        if ($this->tracing) {
            $this->__last_response = $responseEvent->getResponse()->saveXML();
            $this->__last_response_headers = $requestEvent->getResponseHeaders();
        }
        
        return $responseEvent->getResponse()->saveXML();
    }

    /**
     * Get the WSDL file
     *
     * @param mixed $uri
     * @return void
     */
    private function getWsdl($uri)
    {
        $wsdl = $this->eventDispatcher->dispatch(Events::WSDL_REQUEST, new WsdlRequestEvent($uri))->getWsdl();

        if (!$wsdl) {
            throw new \RuntimeException(sprintf('Could not load WSDL from "%s"', $uri));
        }

        $dom = new \DomDocument();
        $dom->loadXML($wsdl);

        $event = new WsdlResponseEvent($dom);
        $this->eventDispatcher->dispatch(Events::WSDL_RESPONSE, $event);
        
        return $event->getWsdl()->saveXML();
    }
}
