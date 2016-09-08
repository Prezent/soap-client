<?php

namespace Prezent\Soap\Client\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * CallEvent
 *
 * @see Event
 * @author Sander Marechal
 */
class CallEvent extends Event
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var mixed
     */
    private $arguments;

    /**
     * Constructor
     *
     * @param string $method
     * @param mixed $arguments
     */
    public function __construct($method, $arguments)
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Set method
     *
     * @param string $method
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get arguments
     *
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }
    
    /**
     * Set arguments
     *
     * @param mixed $arguments
     * @return self
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
}
