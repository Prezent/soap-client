<?php

namespace Prezent\Soap\Client;

/**
 * Contains all the events dispatched by the SoapClient
 *
 * @author Sander Marechal
 */
final class Events
{
    /**
     * The WSDL_REQUEST event is triggered during construction of the SOAP client to load the WSDL file.
     *
     * Listeners of this event are passed a Prezent\Soap\Client\Event\WsdlRequestEvent instance. To implement your
     * own transport, Listeners can set the contents of the WSDL file as a string and stop propagation. Example:
     *
     *     $client->__addListener(Events::WSDL_REQUEST, function (WsdlRequestEvent $event) {
     *         $response = $guzzle->get($event->getUri());
     *         $event->setWsdl($response->getBody());
     *         $event->stopPropagation();
     *     });
     */
    const WSDL_REQUEST = 'wsdl.request';

    /**
     * The WSDL_RESPONSE event is triggered during construction of the SOAP client, after the WSDL file has
     * been loaded. Listeners can use this event to parse the WSDL file themselves, for example for features not
     * supported by the built-in SoapClient.
     *
     * Listeners of this event are passen a Prezent\Soap\Client\Event\WsdlResponseEvent. Any changes make to the
     * WSDL DOMDocument are passed to the built-in SoapClient. Example:
     *
     *     $client->__addListener(Events::WSDL_RESPONSE, function(WsdlResponseEvent $event) {
     *         // Fix bad WSDL
     *         $wsdl = $event->getWsdl();
     *     });
     */
    const WSDL_RESPONSE = 'wsdl.response';

    const CALL = 'soap.call';
    const REQUEST = 'soap.request';
    const RESPONSE = 'soap.response';
    const FINISH = 'soap.finish';

    /**
     * The FAULT event is triggered when a SOAP call has thrown a SoapFault. Listeners are passed a
     * Prezent\Soap\Client\Event\FaultEvent instance.
     *
     * If propagation is stopped on this event then the fault
     * will be considered as handled. Any response provided to the event will be returned. If propagation
     * is not stopped then the fault will be re-thrown. Example:
     *
     *     $client->__addListener(Events::FAULT, function(FaultEvent $event) use ($backup) {
     *         $event->setResponse($backup->getResponse());
     *         $event->stopPropagation();
     *     });
     */
    const FAULT = 'soap.fault';
}
