<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Middleware;

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

interface MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response.
     *
     * Process an incoming server request and either return a response or
     * delegate to the next middleware component to create a response.
     *
     * @param Request $request The request
     * @param callable $next The next middleware to process the request
     * @return Response The response
     */
    public function process(Request $request, callable $next): Response;
} 