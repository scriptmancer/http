<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Middleware;

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

class CacheMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $cacheHandler;

    /**
     * @var array
     */
    private array $options;

    /**
     * Constructor
     *
     * @param callable $cacheHandler A callable that handles cache storage
     * @param array $options Cache options
     */
    public function __construct(callable $cacheHandler, array $options = [])
    {
        $this->cacheHandler = $cacheHandler;
        $this->options = array_merge([
            'ttl' => 3600, // Cache TTL in seconds
            'methods' => ['GET'], // HTTP methods to cache
            'statusCodes' => [200, 203, 204, 300, 301, 302, 304, 404, 410], // Status codes to cache
            'respectCacheControl' => true, // Whether to respect Cache-Control headers
            'headerBlacklist' => [], // Headers that will prevent caching
            'cacheKeyResolver' => null, // Custom cache key resolver
        ], $options);
    }

    /**
     * Process the request and apply caching
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next): Response
    {
        // Only cache specified HTTP methods
        if (!in_array($request->method(), $this->options['methods'])) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = $this->getCacheKey($request);

        // Check if we have a cached response
        $cachedResponse = $this->getCachedResponse($cacheKey);
        if ($cachedResponse) {
            return $cachedResponse;
        }

        // Process the request
        $response = $next($request);

        // Check if we should cache this response
        if ($this->shouldCacheResponse($request, $response)) {
            $this->cacheResponse($cacheKey, $response);
        }

        return $response;
    }

    /**
     * Get a cache key for the request
     *
     * @param Request $request
     * @return string
     */
    private function getCacheKey(Request $request): string
    {
        if (is_callable($this->options['cacheKeyResolver'])) {
            return call_user_func($this->options['cacheKeyResolver'], $request);
        }

        // Default to using the method and URI as the cache key
        return md5($request->method() . '|' . (string) $request->uri());
    }

    /**
     * Get a cached response for a cache key
     *
     * @param string $cacheKey
     * @return Response|null
     */
    private function getCachedResponse(string $cacheKey): ?Response
    {
        $handler = $this->cacheHandler;
        $data = $handler('get', $cacheKey);

        if ($data) {
            return unserialize($data);
        }

        return null;
    }

    /**
     * Cache a response
     *
     * @param string $cacheKey
     * @param Response $response
     * @return void
     */
    private function cacheResponse(string $cacheKey, Response $response): void
    {
        $handler = $this->cacheHandler;
        $ttl = $this->resolveTtl($response);

        $handler('set', $cacheKey, serialize($response), $ttl);
    }

    /**
     * Determine if a response should be cached
     *
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    private function shouldCacheResponse(Request $request, Response $response): bool
    {
        // Check if the status code is cacheable
        if (!in_array($response->getStatusCode(), $this->options['statusCodes'])) {
            return false;
        }

        // Check for blacklisted headers
        foreach ($this->options['headerBlacklist'] as $header) {
            if (!empty($response->getHeader($header))) {
                return false;
            }
        }

        // Check Cache-Control headers if configured to respect them
        if ($this->options['respectCacheControl']) {
            $cacheControl = $response->getHeader('Cache-Control');
            if (!empty($cacheControl)) {
                $directives = explode(',', strtolower($cacheControl[0]));
                foreach ($directives as $directive) {
                    $directive = trim($directive);
                    if ($directive === 'no-store' || $directive === 'no-cache' || $directive === 'private') {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Resolve the TTL for a response
     *
     * @param Response $response
     * @return int
     */
    private function resolveTtl(Response $response): int
    {
        $maxAge = $this->extractMaxAge($response);
        if ($maxAge !== null) {
            return $maxAge;
        }

        return $this->options['ttl'];
    }

    /**
     * Extract max-age from Cache-Control header
     *
     * @param Response $response
     * @return int|null
     */
    private function extractMaxAge(Response $response): ?int
    {
        $cacheControl = $response->getHeader('Cache-Control');
        if (empty($cacheControl)) {
            return null;
        }

        $directives = explode(',', strtolower($cacheControl[0]));
        foreach ($directives as $directive) {
            $directive = trim($directive);
            if (strpos($directive, 'max-age=') === 0) {
                $maxAge = (int) substr($directive, 8);
                return $maxAge > 0 ? $maxAge : null;
            }
        }

        return null;
    }
} 