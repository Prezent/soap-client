<?php

namespace Prezent\Soap\Client\Tests;

use Prezent\Soap\Client\SoapClient;
use Prezent\Soap\Client\Event\RequestEvent;
use Prezent\Soap\Client\Event\ResponseEvent;
use Prezent\Soap\Client\Event\WsdlRequestEvent;
use Prezent\Soap\Client\Event\WsdlResponseEvent;
use Prezent\Soap\Client\Events;

class SoapClientTest extends \PHPUnit_Framework_TestCase
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
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::WSDL_RESPONSE, function (WsdlResponseEvent $event) {
                $this->assertInstanceOf(\DOMDocument::class, $event->getWsdl());
                
                foreach ($event->getWsdl()->getElementsByTagNameNS(self::NS_WSDL, 'operation') as $node) {
                    $node->setAttribute('name', 'sayGoodbye');
                }
            }]
        ]]);

        $functions = $client->__getFunctions();

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

        $client = new SoapClient($uri, ['event_listeners' => [
            [Events::REQUEST, function (RequestEvent $event) {
                $this->assertInstanceOf(\DOMDocument::class, $event->getRequest());
            }]
        ]]);

        $response = $client->sayHello('World');

        $this->assertEquals('Hello World!', $response);
    }

    /**
     * Test request using custom transport
     */
    public function testRequestLoading()
    {
        $client = new SoapClient(__DIR__ . '/Fixtures/hello-world.wsdl', ['event_listeners' => [
            [Events::REQUEST, function (RequestEvent $event) {
                $response = new \DOMDocument();
                $response->loadXML(<<<XML
<SOAP-ENV:Envelope
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ns1="urn:examples:helloservice"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
    SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
    <SOAP-ENV:Body>
        <ns1:sayHelloResponse>
            <greeting xsi:type="xsd:string">Hello you!</greeting>
        </ns1:sayHelloResponse>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML
                );

                $event->setResponse($response);
                $event->stopPropagation();
            }]
        ]]);

        $response = $client->sayHello('World');

        $this->assertEquals('Hello you!', $response);
    }
}
