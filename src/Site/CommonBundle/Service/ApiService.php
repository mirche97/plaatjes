<?php

namespace Site\CommonBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Buzz\Client\Curl as Client;
use Buzz\Message\Request;
use Buzz\Message\Response;

/**
 * The ApiService class provides a single point of exit for all
 * frontend applications. All calls that need to be made to the
 * ctbackend API server have to go through this service. The
 * service is a proxy object to provide access to a third party
 * HTTP client bundle. This wrapper is needed to make sure that
 * any changes made to the way of communication only have to be
 * edited at one location.
 *
 * @author Berry Ligtermoet <berry.ligtermoet@cruisetravel.nl>
 */
class ApiService
{

    /**
     * The Buzz client
     *
     * @var Buzz\Browser
     */
    protected $client;

    /**
     * The Symfony services container
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Construct a new ApiService insance
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->client = new Client();
    }

    /**
     * Send a request to the API
     *
     * @param string $resource   The resource string
     * @param array  $parameters The query string parameter array
     * @param string $method     The HTTP method string
     * @param string $content    The request content body
     * @param array  $headers    The header array
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \RuntimeException
     * @return \Buzz\Message\Response
     */
    public function send($resource, $parameters = array(), $method = Request::METHOD_GET, $content = '', $headers = array())
    {
        // Set up a client, request and response object
        $client = $this->client;
        $client->setTimeout(10000);
        $request = new Request();
        $response = new Response();

        // Parse parameters if passed
        if (!empty($parameters) && is_array($parameters)) {
            $resource .= sprintf('?%s', $this->flattenParameters($parameters));
        }

        // Build request
        $request->setHost($this->container->getParameter('api_backend'));
        $request->setResource($resource);
        $request->setMethod($method);
        $request->setContent($content);
        $request->addHeaders($headers);

        // Send the damn thing already
        $client->send($request, $response);

        // Is the request handled successfully?
        if (!$response->isSuccessful()) {
            $message = sprintf('API request %s %s replied %s', $request->getMethod(), $request->getUrl(), $response->getContent());

            // Log erroneous api request/response
            $logger = $this->container->get('logger');
            $logger->err($message);

            // Throw exception
            throw new HttpException($response->getStatusCode(), $message, null, $response->getHeaders());
        }

        return $response;
    }

    /**
     * Flatten the passed parameter array into a string
     *
     * @param array $parameters The query string parameter array
     *
     * @return string
     */
    public function flattenParameters($parameters)
    {
        foreach ($parameters as $key => &$value) {
            $value = sprintf('%s=%s', $key, urlencode($value));
        }

        return implode('&', $parameters);
    }

}