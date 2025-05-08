<?php

declare(strict_types=1);

namespace Nazim\Http\Client;

use GuzzleHttp\Client as GuzzleClient;
use Nazim\Http\Response\Response;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private GuzzleClient $client;

    public function __construct(array $config = [])
    {
        $this->client = new GuzzleClient($config);
    }

    /**
     * Make a GET request
     */
    public function get(string $uri, array $options = []): Response
    {
        return $this->request('GET', $uri, $options);
    }

    /**
     * Make a POST request
     */
    public function post(string $uri, array $options = []): Response
    {
        return $this->request('POST', $uri, $options);
    }

    /**
     * Make a PUT request
     */
    public function put(string $uri, array $options = []): Response
    {
        return $this->request('PUT', $uri, $options);
    }

    /**
     * Make a PATCH request
     */
    public function patch(string $uri, array $options = []): Response
    {
        return $this->request('PATCH', $uri, $options);
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $uri, array $options = []): Response
    {
        return $this->request('DELETE', $uri, $options);
    }

    /**
     * Make a request
     */
    public function request(string $method, string $uri, array $options = []): Response
    {
        $response = $this->client->request($method, $uri, $options);
        return $this->createResponse($response);
    }

    /**
     * Create a response from a PSR-7 response
     */
    private function createResponse(ResponseInterface $response): Response
    {
        return Response::fromPsrResponse($response);
    }

    /**
     * Get the underlying Guzzle client
     */
    public function getGuzzleClient(): GuzzleClient
    {
        return $this->client;
    }
} 