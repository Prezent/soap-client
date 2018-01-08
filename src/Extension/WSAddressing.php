<?php

namespace Prezent\Soap\Client\Extension;

use Ramsey\Uuid\Uuid;
use Prezent\Soap\Client\Event\RequestEvent;
use Prezent\Soap\Client\Event\WsdlResponseEvent;
use Prezent\Soap\Client\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add WS-Addressing support to the SoapClient
 *
 * @see EventSubscriberInterface
 * @author Sander Marechal
 */
class WSAddressing implements EventSubscriberInterface
{
    /**
     * XML namespaces
     */
    const NS_WSA = 'http://www.w3.org/2005/08/addressing';
    const NS_WSAW = 'http://www.w3.org/2006/05/addressing/wsdl';
    const NS_XMLNS = 'http://www.w3.org/2000/xmlns/';

    /**
     * Default ReplyTo and From address
     */
    const ANONYMOUS = 'http://schemas.xmlsoap.org/ws/2004/08/addressing/role/anonymous';

    /**
     * @var string Reply-to address
     */
    private $address;

    /**
     * @var string From address of source endpoint
     */
    private $fromAddress = self::ANONYMOUS;

    /**
     * @var bool include From header
     */
    private $includeFrom = false;

    /**
     * @var bool
     */
    private $wsaEnabled = false;

    /**
     * Constructor
     *
     * @param string $address Reply-to address
     * @param bool $force Force WSA in non-WSDL mode
     */
    public function __construct($address = null, $force = false)
    {
        $this->address = $address;
        $this->wsaEnabled = $force;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::WSDL_RESPONSE => ['onWsdlResponse', 10],
            Events::REQUEST => ['onRequest', 10],
        ];
    }

    /**
     * Parse the WSDL to check if WS-Addressing should be enabled
     *
     * @param WsdlResponseEvent $event
     * @return void
     */
    public function onWsdlResponse(WsdlResponseEvent $event)
    {
        $xpath = new \DOMXPath($event->getWsdl());
        $xpath->registerNamespace('wsaw', self::NS_WSAW);

        // This is a pretty dumb check. Just see if a wsaw:Addressing or wsaw:UsingAddressing tag exists
        if ($xpath->query('//wsaw:Addressing')->length || $xpath->query('//wsaw:UsingAddressing')->length) {
            $this->wsaEnabled = true;
        }
    }

    /**
     * Add WS-Addressing headers to the request
     *
     * @param RequestEvent $event
     * @return void
     */
    public function onRequest(RequestEvent $event)
    {
        if (!$this->wsaEnabled) {
            return;
        }

        $request = $event->getRequest();
        $envelope = $request->documentElement;
        $soapNS = $envelope->namespaceURI;

        $xpath = new \DOMXPath($event->getRequest());
        $xpath->registerNamespace('soap', $soapNS);
        $xpath->registerNamespace('wsa', self::NS_WSA);

        // Add WSA namespace to envelope
        $envelope->setAttributeNS(self::NS_XMLNS, 'xmlns:wsa', self::NS_WSA);

        // Find or create header
        $headers = $xpath->query('//soap:Envelope/soap:Header');
        $header = $headers->item(0);

        if (!$header) {
            $header = $request->createElementNS($soapNS, $envelope->prefix . ':Header');
            $envelope->insertBefore($header, $envelope->firstChild);
        }

        // Add MessageID
        $header->appendChild($request->createElementNS(self::NS_WSA, 'wsa:MessageID', 'uuid:' . Uuid::uuid4()));

        // Add Action
        $header->appendChild($request->createElementNS(self::NS_WSA, 'wsa:Action', $event->getAction()));

        // Add To
        $header->appendChild($request->createElementNS(self::NS_WSA, 'wsa:To', $event->getLocation()));

        // Add From
        if ($this->isIncludeFrom()) {
            $fromAddress = $this->fromAddress ?: self::ANONYMOUS;
            $from = $request->createElementNS(self::NS_WSA, 'wsa:From');
            $from->appendChild($request->createElementNS(self::NS_WSA, 'wsa:Address', $fromAddress));
            $header->appendChild($from);
        }

        // Add ReplyTo
        if (!$event->isOneWay()) {
            $address = $this->address ?: self::ANONYMOUS;

            $replyTo = $request->createElementNS(self::NS_WSA, 'wsa:ReplyTo');
            $replyTo->appendChild($request->createElementNS(self::NS_WSA, 'wsa:Address', $address));

            $header->appendChild($replyTo);
        }
    }

    /**
     * Manually enable WS-Addressing
     *
     * @return self
     */
    public function enable()
    {
        $this->wsaEnabled = true;
        return $this;
    }

    /**
     * Manually disable WS-Addressing
     *
     * @return self
     */
    public function disable()
    {
        $this->wsaEnabled = false;
        return $this;
    }

    /**
     * Check if WS-Addressing is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->wsaEnabled;
    }

    /**
     * Getter for fromAddress
     *
     * @return string
     */
    public function getFromAddress()
    {
        return $this->fromAddress;
    }

    /**
     * Setter for fromAddress
     *
     * @param string $fromAddress
     * @return self
     */
    public function setFromAddress($fromAddress)
    {
        $this->fromAddress = $fromAddress;

        return $this;
    }

    /**
     * Isser for includeFrom
     *
     * @return bool
     */
    public function isIncludeFrom()
    {
        return $this->includeFrom;
    }

    /**
     * Setter for includeFrom
     *
     * @param bool $includeFrom
     * @return self
     */
    public function setIncludeFrom($includeFrom)
    {
        $this->includeFrom = $includeFrom;

        return $this;
    }
}
