<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Middleware;

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;
use Scriptmancer\Http\Session\Session;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * Constructor
     *
     * @param array $options Session options
     */
    public function __construct(array $options = [])
    {
        $this->session = new Session($options);
    }

    /**
     * Process the request and initialize the session
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next): Response
    {
        // Start the session
        $this->session->start();
        
        // Add the session to the request attributes
        $serverRequest = $request->getPsrRequest()->withAttribute('session', $this->session);
        $request = new Request($serverRequest);
        
        // Process the request through the rest of the middleware stack and controller
        $response = $next($request);
        
        return $response;
    }

    /**
     * Get the session instance
     *
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }
} 