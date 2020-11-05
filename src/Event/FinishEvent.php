<?php

namespace Prezent\Soap\Client\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * FinishEvent
 *
 * @see Event
 * @author Sander Marechal
 */
class FinishEvent extends Event
{
    /**
     * @var mixed
     */
    private $response;

    /**
     * Constructor
     *
     * @param mixed $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get response
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response
     *
     * @param mixed $response
     * @return self
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
}
