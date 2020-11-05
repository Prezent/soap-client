<?php

namespace Prezent\Soap\Client\Tests;

use PHPUnit\Framework\TestCase;
use Prezent\Soap\Client\SoapClient;
use Prezent\Soap\Client\Event\CallEvent;
use Prezent\Soap\Client\Event\FaultEvent;
use Prezent\Soap\Client\Event\FinishEvent;
use Prezent\Soap\Client\Event\RequestEvent;
use Prezent\Soap\Client\Event\ResponseEvent;
use Prezent\Soap\Client\Event\WsdlRequestEvent;
use Prezent\Soap\Client\Event\WsdlResponseEvent;
use Prezent\Soap\Client\Events;
use SoapFault;

class SoapClientTest extends TestCase
{
    const NS_WSDL = 'http://schemas.xmlsoap.org/wsdl/';

    /**
     * Test changing the WSDL uri
     */
    public function testWsdlRequestUri()
    {
        $client = new SoapClient(null, ['event_listeners' => [
            [Events::WSDL_REQUEST, function (WsdlRequestEvent $event) {
                $event->setUri(__DIR__ . '/Fixtures/hello-world.wsdl');
            }]
        ]]);

        $functions = $client->__getFunctions();

        $this->assertCount(1, $functions);
        $this->assertEquals('string sayHello(string $firstName)', $functions[0]);
    }

    /**
     * Test overriding WSDL loading
     */
    public function testWsdlRequestLoading()
    {
        $client = new SoapClient(null, ['event_listeners' => [
            [Events::WSDL_REQUEST, function (WsdlRequestEvent $event) {
                $event->setWsdl(file_get_contents(__DIR__ . '/Fixtures/hello-world.wsdl'));
                $event->stopPropagation();
            }]
        ]]);

        $functions = $client->__getFunctions();

        $this->assertCount(1, $functions);
        $this->assertEquals('string sayHello(string $firstName)', $functions[0]);
    }

    /**
     * Test parsing/changing the WSDL
     */
    public function testWsdlResponse()
    {
        $wsdl = null;

        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::WSDL_RESPONSE, function (WsdlResponseEvent $event) use (&$wsdl) {
                $wsdl = $event->getWsdl();

                foreach ($event->getWsdl()->getElementsByTagNameNS(self::NS_WSDL, 'operation') as $node) {
                    $node->setAttribute('name', 'sayGoodbye');
                }
            }]
        ]]);

        $functions = $client->__getFunctions();

        $this->assertInstanceOf(\DOMDocument::class, $wsdl);
        $this->assertCount(1, $functions);
        $this->assertEquals('string sayGoodbye(string $firstName)', $functions[0]);
    }

    /**
     * Test requests using the built-in transport
     *
     * @group webserver
     */
    public function testRequest()
    {
        $uri = 'http://' . WEB_SERVER_HOSTNAME . ':' . WEB_SERVER_PORT . '/HelloService.php?wsdl';
        $request = null;

        $client = new SoapClient($uri, ['event_listeners' => [
            [Events::REQUEST, function (RequestEvent $event) use (&$request) {
                $request = $event->getRequest();
            }]
        ]]);

        $response = $client->sayHello('World');

        $this->assertInstanceOf(\DOMDocument::class, $request);
        $this->assertEquals('Hello, World!', $response);
    }

    /**
     * Test request using custom transport
     */
    public function testRequestLoading()
    {
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, [$this, 'handleRequest']]
        ]]);

        $this->assertEquals('Hello, World!', $client->sayHello('me'));
    }

    public function testRequestException()
    {
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, function (RequestEvent $event) {
                throw new \RuntimeException('Test exception');
            }]
        ]]);

        $this->expectException(SoapFault::class);

        $response = $client->sayHello('World');
    }

    /**
     * Test handling of non-SOAP responses
     */
    public function testNonSoapResponse()
    {
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, function (RequestEvent $event) {
                $event->setResponse('Plain text');
                $event->stopPropagation();
            }]
        ]]);

        try {
            $response = $client->sayHello('World');
        } catch (\SoapFault $f) {
            $this->assertInstanceOf(\SoapFault::class, $f);
            $this->assertEquals('Plain text', $client->__getLastResponse());
            return;
        }

        $this->fail('No \SoapFault found');
    }

    /**
     * Test request tracing
     */
    public function testTracing()
    {
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['trace' => true, 'event_listeners' => [
            // Modify the request to test that the modified request is stored
            [Events::REQUEST, function (RequestEvent $event) {
                $dom = new \DOMDocument();
                $dom->loadXML(str_replace('New York', 'World', $event->getRequest()->saveXML()));

                $event->setRequest($dom);
            }],

            // Generate custom response and set request/response headers
            [Events::REQUEST, [$this, 'handleRequest']],

            // Modify the response to test that the modified response is stored
            [Events::RESPONSE, function (ResponseEvent $event) {
                $dom = new \DOMDocument();
                $dom->loadXML(str_replace('World', 'Universe', $event->getResponse()->saveXML()));

                $event->setResponse($dom);
            }],
        ]]);

        $response = $client->sayHello('New York');

        $this->assertEquals('X-Header: request', $client->__getLastRequestHeaders());
        $this->assertEquals('X-Header: response', $client->__getLastResponseHeaders());
        $this->assertStringContainsString('World', $client->__getLastRequest());
        $this->assertStringContainsString('Hello, Universe', $client->__getLastResponse());
    }

    public function testResponseException()
    {
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, [$this, 'handleRequest']],
            [Events::RESPONSE, function (ResponseEvent $event) {
                throw new \RuntimeException('Test exception');
            }]
        ]]);

        $this->expectException(SoapFault::class);

        $response = $client->sayHello('World');
    }

    /**
     * Test fault event is triggered and re-thrown
     *
     * @return void
     */
    public function testFaultEvent()
    {
        $fault = null;

        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, function (RequestEvent $event) {
                throw new \RuntimeException('Test exception');
            }],
            [Events::FAULT, function (FaultEvent $event) use (&$fault) {
                $fault = $event->getFault();
            }]
        ]]);

        try {
            $response = $client->sayHello('World');
        } catch (\SoapFault $e) {
            $this->assertInstanceOf(\SoapFault::class, $e);
        }

        $this->assertInstanceOf(\SoapFault::class, $fault);
    }

    /**
     * Test handling fault by stopping propagation
     */
    public function testHandleFault()
    {
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, [$this, 'handleRequest']],
            [Events::RESPONSE, function (ResponseEvent $event) {
                throw new \RuntimeException('Test exception');
            }],
            [Events::FAULT, function (FaultEvent $event) {
                $event->setResponse('Dummy response');
                $event->stopPropagation();
            }]
        ]]);

        $response = $client->sayHello('World');
        $this->assertEquals('Dummy response', $response);
    }

    /**
     * Test call/finish handling for __call
     */
    public function testCallEvents()
    {
        $callEvent = null;
        $finishEvent = null;

        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, [$this, 'handleRequest']],
            [Events::CALL, function (CallEvent $event) use (&$callEvent) {
                $callEvent = $event;
            }],
            [Events::FINISH, function (FinishEvent $event) use (&$finishEvent) {
                $finishEvent = $event;
            }]
        ]]);

        $client->sayHello('World');

        $this->assertEquals('sayHello', $callEvent->getMethod());
        $this->assertEquals(['World'], $callEvent->getArguments());
        $this->assertEquals('Hello, World!', $finishEvent->getResponse());
    }

    /**
     * Test call/finish handling for __SoapCall
     */
    public function testSoapCallEvents()
    {
        $callEvent = null;
        $finishEvent = null;

        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, [$this, 'handleRequest']],
            [Events::CALL, function (CallEvent $event) use (&$callEvent) {
                $callEvent = $event;
            }],
            [Events::FINISH, function (FinishEvent $event) use (&$finishEvent) {
                $finishEvent = $event;
            }]
        ]]);

        $client->__soapCall('sayHello', ['World']);

        $this->assertEquals('sayHello', $callEvent->getMethod());
        $this->assertEquals(['World'], $callEvent->getArguments());
        $this->assertEquals('Hello, World!', $finishEvent->getResponse());
    }

    /**
     * Event listener to generate a "Hello, World!" response
     *
     * @param RequestEvent $event
     * @return void
     */
    public function handleRequest(RequestEvent $event)
    {
        $xml = <<<XML
<SOAP-ENV:Envelope
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ns1="urn:examples:helloservice"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
    SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
    <SOAP-ENV:Body>
        <ns1:sayHelloResponse>
            <greeting xsi:type="xsd:string">Hello, World!</greeting>
        </ns1:sayHelloResponse>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;

        $event->setRequestHeaders('X-Header: request');
        $event->setResponse($xml);
        $event->setResponseHeaders('X-Header: response');
        $event->stopPropagation();
    }
}
