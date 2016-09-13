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

    /**
     * The CALL event is triggered just after calling a SOAP method.
     *
     * The listener is passed a Prezent\Soap\Client\Event\CallEvent instance. It allows changing the method
     * and arguments for e.g. custom marshalling. The final arguments should be a value that van be understood
     * by PHP's built-in SoapClient. Example:
     *
     *     $client->__addListener(Events::CALL, function(CallEvent $event) {
     *         $arguments = $serializer->serialize($event->getArguments());
     *         $event->setArguments($arguments);
     *     });
     */
    const CALL = 'soap.call';

    /**
     * The REQUEST event is triggered when a SOAP request is about to be sent over a transport.
     *
     * The listener is passed a Prezent\Soap\Client\Event\RequestEvent instance. The request and any
     * parameters may be modified here. When implementing a custom transport, you can set the response
     * as a string on the event and stop propagation. Optionally you can also set the request and response
     * headers to facilitate tracing.
     *
     * All exceptions are converted to \SoapFault.
     *
     * Example:
     *
     *     $client->__addListener(Events::REQUEST, function(RequestEvent $event) {
     *         $response = $guzzle->request('POST', $event->getLocation(), [
     *             'body' => $event->getRequest()->saveXML(),
     *         ]);
     *
     *         $event->setResponse($response->getBody());
     *         $event->stopPropagation();
     *     });
     */
    const REQUEST = 'soap.request';

    /**
     * The RESPONSE event is triggered when a SOAP response has been recieved. The listener is passed a
     * Prezent\Soap\Client\Event\ResponseEvent instance. The response can be modified before being processed
     * by the rest of the client.
     *
     * All exceptions are converted to \SoapFault.
     *
     * Example:
     *
     *     $client->__addListener(Events::RESPONSE, function(ResponseEvent $event) {
     *         $logger->log($event->getResponse()->saveXML());
     *     });
     */
    const RESPONSE = 'soap.response';

    /**
     * The FINISH event is triggered just before returning from a SOAP method. Listeners are passed a
     * Prezent\Soap\Client\Event\FinishEvent instance. It allows changing the response for e.g. custom marshalling.
     *
     * The initial response is a value, array or object as returned by PHP's built-in SoapClient. Example:
     *
     *     $client->__addListener(Events::FINISH, function(FinishEvent $event) {
     *         $response = $serializer->unserialize(MyResponse::class, $event->getResponse());
     *         $event->setResponse($response);
     *     });
     */
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
