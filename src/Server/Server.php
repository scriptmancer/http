<?php

declare(strict_types=1);

namespace Nazim\Http\Server;

use Nazim\Http\Exception\HttpException;
use Nazim\Http\Middleware\MiddlewareInterface;
use Nazim\Http\Middleware\MiddlewareStack;
use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;
use Throwable;

class Server
{
    /**
     * @var MiddlewareStack
     */
    private MiddlewareStack $middlewareStack;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middlewareStack = new MiddlewareStack();
    }

    /**
     * Add middleware to the stack
     *
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewareStack->add($middleware);
        return $this;
    }

    /**
     * Handle a request and return a response
     *
     * @param Request $request The incoming HTTP request
     * @param callable $handler A callable that handles the request and returns a response
     * @return Response The HTTP response
     */
    public function handle(Request $request, callable $handler): Response
    {
        try {
            return $this->middlewareStack->process($request, function (Request $req) use ($handler) {
                return $this->process($req, $handler);
            });
        } catch (HttpException $e) {
            return $this->createExceptionResponse($e);
        } catch (Throwable $e) {
            return $this->createErrorResponse($e);
        }
    }

    /**
     * Process a request through the handler
     *
     * @param Request $request The incoming HTTP request
     * @param callable $handler A callable that handles the request and returns a response
     * @return Response The HTTP response
     */
    protected function process(Request $request, callable $handler): Response
    {
        $response = $handler($request);

        if (!$response instanceof Response) {
            throw new \RuntimeException(
                sprintf(
                    'The handler must return an instance of %s, %s returned',
                    Response::class,
                    is_object($response) ? get_class($response) : gettype($response)
                )
            );
        }

        return $response;
    }

    /**
     * Create a response from an HTTP exception
     *
     * @param HttpException $exception The HTTP exception
     * @return Response The HTTP response
     */
    protected function createExceptionResponse(HttpException $exception): Response
    {
        return new Response(
            $exception->getMessage(),
            $exception->getStatusCode(),
            $exception->getHeaders()
        );
    }

    /**
     * Create a response from a generic error
     *
     * @param Throwable $exception The exception
     * @return Response The HTTP response
     */
    protected function createErrorResponse(Throwable $exception): Response
    {
        return new Response(
            'Internal Server Error',
            500,
            ['X-Error' => $exception->getMessage()]
        );
    }

    /**
     * Send the response to the client
     *
     * @param Response $response The HTTP response
     * @return void
     */
    public function send(Response $response): void
    {
        $this->sendHeaders($response);
        $this->sendBody($response);
    }

    /**
     * Send the response headers
     *
     * @param Response $response The HTTP response
     * @return void
     */
    protected function sendHeaders(Response $response): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
    }

    /**
     * Send the response body
     *
     * @param Response $response The HTTP response
     * @return void
     */
    protected function sendBody(Response $response): void
    {
        $body = $response->getBodyStream();
        
        try {
            // Check if this is a seekable stream
            if ($body->isSeekable()) {
                // Rewind the stream before reading
                $body->rewind();
                
                // Output the content in chunks
                while (!$body->eof()) {
                    echo $body->read(8192); // Read in 8KB chunks
                    flush();
                }
            } else {
                // For non-seekable streams (like our streaming response)
                // Just read until we get an empty string or reach EOF
                while (!$body->eof()) {
                    $chunk = $body->read(8192);
                    if ($chunk === '') {
                        break; // End if we get an empty string
                    }
                    echo $chunk;
                    flush();
                }
            }
        } catch (\Throwable $e) {
            // If streaming fails, fall back to string conversion
            // This is less efficient but more resilient
            error_log('Stream reading failed: ' . $e->getMessage());
            echo (string) $response->getBody();
        }
    }
} 