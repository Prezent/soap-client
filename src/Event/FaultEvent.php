<?php

namespace Prezent\Soap\Client\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * FaultEvent
 *
 * @see Event
 * @author Sander Marechal
 */
class FaultEvent extends Event
{
    /**
     * @var \SoapFault
     */
    private $fault;

    /**
     * @var string
     */
    private $lastRequest;

    /**
     * @var string
     */
    private $lastRequestHeaders;

    /**
     * @var string
     */
    private $lastResponse;

    /**
     * @var string
     */
    private $lastResponseHeaders;

    /**
     * @var mixed
     */
    private $response;

    /**
     * Constructor
     *
     * @param \SoapFault $fault
     * @param string $lastRequest
     * @param string $lastRequestHeaders
     * @param string $lastResponse
     * @param string $lastResponseHeaders
     */
    public function __construct(
        \SoapFault $fault,
        $lastRequest = null,
        $lastRequestHeaders = null,
        $lastResponse = null,
        $lastResponseHeaders = null
    ) {
        $this->fault = $fault;
        $this->lastRequest = $lastRequest;
        $this->lastRequestHeaders = $lastRequestHeaders;
        $this->lastResponse = $lastResponse;
        $this->lastResponseHeaders = $lastResponseHeaders;
    }

    /**
     * Get fault
     *
     * @return \SoapFault
     */
    public function getFault()
    {
        return $this->fault;
    }

    /**
     * Get lastRequest
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Get lastRequestHeaders
     *
     * @return string
     */
    public function getLastRequestHeaders()
    {
        return $this->lastRequestHeaders;
    }

    /**
     * Get lastResponse
     *
     * @return string
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Get lastResponseHeaders
     *
     * @return string
     */
    public function getLastResponseHeaders()
    {
        return $this->lastResponseHeaders;
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
