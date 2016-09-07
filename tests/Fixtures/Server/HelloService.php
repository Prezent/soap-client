<?php

namespace Prezent\Soap\Client\Tests\Fixtures\Server;

// auto-detect URI
$uri = 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, -5);

// Set URI in WSDL
$dom = new \DOMDocument();
$dom->load(__DIR__ . '/../hello-world.wsdl');

foreach ($dom->getElementsByTagNameNS('http://schemas.xmlsoap.org/wsdl/soap/', 'address') as $address) {
    $address->setAttribute('location', $uri);
}

$wsdl = $dom->saveXML();

// Serve WSDL
if (isset($_GET['wsdl'])) {
    echo $dom->saveXML();
    exit();
}

// Service definition
class HelloService
{
    // SOAP function
    public function sayHello($name)
    {
        return 'Hello ' . $name . '!';
    }
}

// Start SOAP server
$server = new \SoapServer('data://text/xml;base64,' . base64_encode($wsdl));
$server->setObject(new HelloService());
$server->handle();
