<?php

declare(strict_types=1);

namespace Nazim\Http\Middleware;

use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;

class ContentTypeMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private string $contentType;

    /**
     * Constructor
     *
     * @param string $contentType
     */
    public function __construct(string $contentType = 'application/json')
    {
        $this->contentType = $contentType;
    }

    /**
     * Process the request and add a content type header to the response
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next): Response
    {
        // Process the request through the rest of the middleware stack and controller
        $response = $next($request);

        // Add or replace the Content-Type header
        return $response->withHeader('Content-Type', $this->contentType);
    }
} 