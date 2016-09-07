<?php

namespace Prezent\Soap\Client\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * RequestEvent
 *
 * @see Event
 * @author Sander Marechal
 */
class RequestEvent extends Event
{
    /**
     * @var \DOMDocument
     */
    private $request;

    /**
     * @var string
     */
    private $requestHeaders;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $action;

    /**
     * @var int
     */
    private $version;

    /**
     * @var bool
     */
    private $oneWay;

    /**
     * @var \DOMDocument
     */
    private $response;

    /**
     * @var string
     */
    private $responseHeaders;

    /**
     * Constructor
     *
     * @param \DOMDocument $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param bool $oneWay
     */
    public function __construct(\DOMDocument $request, $location, $action, $version, $oneWay = false)
    {
        $this->request = $request;
        $this->location = $location;
        $this->action = $action;
        $this->version = $version;
        $this->oneWay = $oneWay;
    }

    /**
     * Get request
     *
     * @return \DOMDocument
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Set request
     *
     * @param \DOMDocument $request
     * @return self
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get requestHeaders
     *
     * @return string
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }
    
    /**
     * Set requestHeaders
     *
     * @param string $requestHeaders
     * @return self
     */
    public function setRequestHeaders($requestHeaders)
    {
        $this->requestHeaders = $requestHeaders;
        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * Set location
     *
     * @param string $location
     * @return self
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Set action
     *
     * @param string $action
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Set version
     *
     * @param int $version
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Is oneWay
     *
     * @return bool
     */
    public function isOneWay()
    {
        return $this->oneWay;
    }
    
    /**
     * Set oneWay
     *
     * @param bool $oneWay
     * @return self
     */
    public function setOneWay($oneWay = true)
    {
        $this->oneWay = $oneWay;
        return $this;
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

    /**
     * Get responseHeaders
     *
     * @return string
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }
    
    /**
     * Set responseHeaders
     *
     * @param string $responseHeaders
     * @return self
     */
    public function setResponseHeaders($responseHeaders)
    {
        $this->responseHeaders = $responseHeaders;
        return $this;
    }
}
