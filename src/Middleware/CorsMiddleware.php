<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Middleware;

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private array $options;

    /**
     * Constructor
     *
     * @param array $options CORS options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'allowedOrigins' => ['*'],
            'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
            'allowedHeaders' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
            'exposedHeaders' => [],
            'maxAge' => 0,
            'allowCredentials' => false,
        ], $options);
    }

    /**
     * Process the request and add CORS headers to the response
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next): Response
    {
        // For preflight OPTIONS requests, return early with headers only
        if ($request->isMethod('OPTIONS')) {
            $response = new Response('', 204);
        } else {
            // Process the request through the rest of the middleware stack and controller
            $response = $next($request);
        }

        // Get the origin from the request
        $origin = $request->header('Origin');

        // If there's no origin header, return the response as is
        if (!$origin) {
            return $response;
        }

        // Check if the origin is allowed
        if (in_array('*', $this->options['allowedOrigins']) || in_array($origin, $this->options['allowedOrigins'])) {
            // Add the Access-Control-Allow-Origin header
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);

            // Add the Access-Control-Allow-Credentials header if needed
            if ($this->options['allowCredentials']) {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }

            // For preflight requests, add the other CORS headers
            if ($request->isMethod('OPTIONS')) {
                // Add the Access-Control-Allow-Methods header
                $response = $response->withHeader('Access-Control-Allow-Methods', implode(', ', $this->options['allowedMethods']));

                // Add the Access-Control-Allow-Headers header
                $response = $response->withHeader('Access-Control-Allow-Headers', implode(', ', $this->options['allowedHeaders']));

                // Add the Access-Control-Max-Age header if needed
                if ($this->options['maxAge'] > 0) {
                    $response = $response->withHeader('Access-Control-Max-Age', (string) $this->options['maxAge']);
                }
            }

            // Add the Access-Control-Expose-Headers header if needed
            if (!empty($this->options['exposedHeaders'])) {
                $response = $response->withHeader('Access-Control-Expose-Headers', implode(', ', $this->options['exposedHeaders']));
            }
        }

        return $response;
    }
} 