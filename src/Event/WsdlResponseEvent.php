<?php

namespace Prezent\Soap\Client\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * WsdlResponseEvent
 *
 * @see Event
 * @author Sander Marechal
 */
class WsdlResponseEvent extends Event
{
    /**
     * @var \DOMDocument
     */
    private $wsdl;

    /**
     * Constructor
     *
     * @param \DOMDocument $wsdl
     */
    public function __construct(\DOMDocument $wsdl)
    {
        $this->wsdl = $wsdl;
    }

    /**
     * Get wsdl
     *
     * @return \DOMDocument
     */
    public function getWsdl()
    {
        return $this->wsdl;
    }
}
