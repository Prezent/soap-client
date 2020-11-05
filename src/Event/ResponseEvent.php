<?php

namespace Prezent\Soap\Client\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * ResponseEvent
 *
 * @see Event
 * @author Sander Marechal
 */
class ResponseEvent extends Event
{
    /**
     * @var \DOMDocument
     */
    private $response;

    /**
     * Constructor
     *
     * @param \DOMDocument $response
     */
    public function __construct(\DOMDocument $response)
    {
        $this->response = $response;
    }

    /**
     * Get response
     *
     * @return \DOMDocument
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response
     *
     * @param \DOMDocument $response
     * @return self
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
}
