<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Middleware;

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

class MiddlewareStack
{
    /**
     * @var array<MiddlewareInterface>
     */
    private array $middleware = [];

    /**
     * Add middleware to the stack
     *
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function add(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Process the request through all middleware
     *
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        return $this->createExecutionChain($handler)($request);
    }

    /**
     * Create the middleware execution chain
     *
     * @param callable $handler
     * @return callable
     */
    private function createExecutionChain(callable $handler): callable
    {
        $next = $handler;

        // Reverse the middleware array to create the chain from end to start
        foreach (array_reverse($this->middleware) as $middleware) {
            $next = function (Request $request) use ($middleware, $next) {
                return $middleware->process($request, $next);
            };
        }

        return $next;
    }
} 