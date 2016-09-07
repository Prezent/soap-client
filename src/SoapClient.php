<?php

namespace Prezent\Soap\Client;

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
     * {@inheritDoc}
     */
    public function __construct($wsdl, array $options = [])
    {
        // Setup stream context
        $this->streamContext = isset($options['stream_context'])
            ? $options['stream_context']
            : stream_context_create();

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

        // Attach built-in listeners
        $this->__addListener(Events::WSDL_REQUEST, [$this, '__loadWsdl'], -999);

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
        $result = parent::__doRequest($request, $location, $action, $version, $oneWay);
        
        return $result;
    }

    /**
     * Load a WSDL file using the built-in stream context
     *
     * @param WsdlRequestEvent $event
     * @return string
     */
    public function __loadWsdl(WsdlRequestEvent $event)
    {
        $event->setWsdl(file_get_contents($event->getUri(), false, $this->streamContext));
        $event->stopPropagation();
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
