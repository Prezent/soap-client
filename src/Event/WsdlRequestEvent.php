<?php

namespace Prezent\Soap\Client\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * WsdlRequestEvent
 *
 * @see Event
 * @author Sander Marechal
 */
class WsdlRequestEvent extends Event
{
    /**
     * @var string URI of the WSDL
     */
    private $uri;

    /**
     * @var string|null Contents of the WSDL
     */
    private $wsdl = null;

    /**
     * Constructor
     *
     * @param mixed $uri
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set uri
     *
     * @param string $uri
     * @return self
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Get wsdl
     *
     * @return string
     */
    public function getWsdl()
    {
        return $this->wsdl;
    }
    
    /**
     * Set wsdl
     *
     * @param string $wsdl
     * @return self
     */
    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;
        return $this;
    }
}
