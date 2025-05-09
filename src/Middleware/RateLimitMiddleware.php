<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Middleware;

use Scriptmancer\Http\Exception\HttpException;
use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private array $options;

    /**
     * @var callable
     */
    private $storageHandler;

    /**
     * Constructor
     *
     * @param callable $storageHandler A callable that handles rate limit storage
     * @param array $options Rate limit options
     */
    public function __construct(callable $storageHandler, array $options = [])
    {
        $this->storageHandler = $storageHandler;
        $this->options = array_merge([
            'limit' => 60, // Number of requests
            'window' => 60, // Time window in seconds
            'identifierResolver' => null, // Custom identifier resolver
            'headers' => true, // Whether to include rate limit headers
        ], $options);
    }

    /**
     * Process the request and apply rate limiting
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     * @throws HttpException When rate limit is exceeded
     */
    public function process(Request $request, callable $next): Response
    {
        // Get the client identifier
        $identifier = $this->getIdentifier($request);
        
        // Get current rate limit data
        $rateLimit = $this->getRateLimit($identifier);
        
        // Check if the rate limit is exceeded
        if ($rateLimit['requests'] >= $this->options['limit']) {
            // Calculate the remaining time until the rate limit resets
            $retryAfter = $rateLimit['expires'] - time();
            
            // Return a 429 Too Many Requests response
            throw new HttpException(
                'Rate limit exceeded. Try again later.',
                429,
                [
                    'Retry-After' => (string) max(0, $retryAfter),
                    'X-RateLimit-Limit' => (string) $this->options['limit'],
                    'X-RateLimit-Remaining' => '0',
                    'X-RateLimit-Reset' => (string) $rateLimit['expires'],
                ]
            );
        }
        
        // Process the request through the rest of the middleware stack and controller
        $response = $next($request);
        
        // Increment the rate limit counter
        $rateLimit = $this->incrementRateLimit($identifier, $rateLimit);
        
        // Add rate limit headers to the response if enabled
        if ($this->options['headers']) {
            $response = $response->withHeader('X-RateLimit-Limit', (string) $this->options['limit'])
                ->withHeader('X-RateLimit-Remaining', (string) max(0, $this->options['limit'] - $rateLimit['requests']))
                ->withHeader('X-RateLimit-Reset', (string) $rateLimit['expires']);
        }
        
        return $response;
    }
    
    /**
     * Get the client identifier from the request
     *
     * @param Request $request
     * @return string
     */
    private function getIdentifier(Request $request): string
    {
        if (is_callable($this->options['identifierResolver'])) {
            return call_user_func($this->options['identifierResolver'], $request);
        }
        
        // Default to using the client IP
        return $request->header('X-Forwarded-For', $request->header('REMOTE_ADDR', 'unknown'));
    }
    
    /**
     * Get the current rate limit for an identifier
     *
     * @param string $identifier
     * @return array
     */
    private function getRateLimit(string $identifier): array
    {
        $storage = $this->storageHandler;
        $data = $storage('get', $identifier);
        
        if (!$data) {
            $data = [
                'requests' => 0,
                'expires' => time() + $this->options['window'],
            ];
        }
        
        // If the rate limit window has expired, reset it
        if (time() > $data['expires']) {
            $data = [
                'requests' => 0,
                'expires' => time() + $this->options['window'],
            ];
        }
        
        return $data;
    }
    
    /**
     * Increment the rate limit counter for an identifier
     *
     * @param string $identifier
     * @param array $rateLimit
     * @return array
     */
    private function incrementRateLimit(string $identifier, array $rateLimit): array
    {
        $rateLimit['requests']++;
        
        $storage = $this->storageHandler;
        $storage('set', $identifier, $rateLimit, $this->options['window']);
        
        return $rateLimit;
    }
} 