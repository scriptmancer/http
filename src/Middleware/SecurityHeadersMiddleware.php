<?php

declare(strict_types=1);

namespace Nazim\Http\Middleware;

use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private array $headers;

    /**
     * Constructor
     *
     * @param array $headers Custom security headers to override defaults
     */
    public function __construct(array $headers = [])
    {
        $defaultHeaders = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'",
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ];

        $this->headers = array_merge($defaultHeaders, $headers);
    }

    /**
     * Process the request and add security headers to the response
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next): Response
    {
        // Process the request through the rest of the middleware stack and controller
        $response = $next($request);

        // Add security headers
        foreach ($this->headers as $name => $value) {
            // Skip empty values (allows effectively disabling a default header)
            if ($value === null || $value === '') {
                continue;
            }
            
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
} 