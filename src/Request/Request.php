<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Request;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\UploadedFile;
use GuzzleHttp\Psr7\Uri;
use Scriptmancer\Http\Cookie\CookieJar;
use Scriptmancer\Http\Session\Session;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Request
{
    private ServerRequestInterface $serverRequest;
    private ?CookieJar $cookieJar = null;

    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * Create a new request from PHP globals
     */
    public static function fromGlobals(): self
    {
        return new self(ServerRequest::fromGlobals());
    }

    /**
     * Create a request with custom parameters
     * 
     * @param string $method HTTP method
     * @param string|UriInterface $uri URI for the request
     * @param array $headers Request headers
     * @param string|resource|null $body Request body
     * @param string $version Protocol version
     * @param array $serverParams Server parameters
     * @return self
     */
    public static function create(
        string $method,
        string|UriInterface $uri,
        array $headers = [],
        mixed $body = null,
        string $version = '1.1',
        array $serverParams = []
    ): self {
        $uri = is_string($uri) ? new Uri($uri) : $uri;
        $request = new ServerRequest($method, $uri, $headers, $body, $version, $serverParams);
        
        return new self($request);
    }

    /**
     * Get the request method
     */
    public function method(): string
    {
        return $this->serverRequest->getMethod();
    }

    /**
     * Check if request method matches the given method
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($this->method()) === strtoupper($method);
    }

    /**
     * Get the request URI
     */
    public function uri(): UriInterface
    {
        return $this->serverRequest->getUri();
    }

    /**
     * Get a request header
     */
    public function header(string $name, ?string $default = null): ?string
    {
        $headers = $this->serverRequest->getHeader($name);
        return $headers[0] ?? $default;
    }

    /**
     * Get all request headers
     */
    public function headers(): array
    {
        return $this->serverRequest->getHeaders();
    }

    /**
     * Get a query parameter
     */
    public function query(string $key, mixed $default = null): mixed
    {
        $params = $this->serverRequest->getQueryParams();
        return $params[$key] ?? $default;
    }

    /**
     * Get all query parameters
     */
    public function queryParams(): array
    {
        return $this->serverRequest->getQueryParams();
    }

    /**
     * Get a request body parameter
     */
    public function post(string $key, mixed $default = null): mixed
    {
        $params = $this->serverRequest->getParsedBody();
        return $params[$key] ?? $default;
    }

    /**
     * Get all request body parameters
     */
    public function postParams(): array
    {
        return $this->serverRequest->getParsedBody() ?? [];
    }

    /**
     * Get an uploaded file
     */
    public function file(string $key): ?UploadedFile
    {
        $files = $this->serverRequest->getUploadedFiles();
        return $files[$key] ?? null;
    }

    /**
     * Get all uploaded files
     */
    public function files(): array
    {
        return $this->serverRequest->getUploadedFiles();
    }

    /**
     * Get all cookies
     */
    public function cookies(): CookieJar
    {
        if ($this->cookieJar === null) {
            $cookieHeader = $this->header('Cookie');
            $this->cookieJar = new CookieJar();
            
            if ($cookieHeader) {
                $this->cookieJar = CookieJar::fromRequestHeaders([$cookieHeader]);
            }
        }
        
        return $this->cookieJar;
    }

    /**
     * Get a cookie by name
     */
    public function cookie(string $name, ?string $default = null): ?string
    {
        $cookie = $this->cookies()->get($name);
        return $cookie ? $cookie->getValue() : $default;
    }

    /**
     * Check if a cookie exists
     */
    public function hasCookie(string $name): bool
    {
        return $this->cookies()->has($name);
    }

    /**
     * Get a request attribute
     */
    public function attribute(string $name, mixed $default = null): mixed
    {
        return $this->serverRequest->getAttribute($name, $default);
    }

    /**
     * Set a request attribute
     */
    public function withAttribute(string $name, mixed $value): self
    {
        $new = clone $this;
        $new->serverRequest = $this->serverRequest->withAttribute($name, $value);
        return $new;
    }

    /**
     * Get all request attributes
     */
    public function attributes(): array
    {
        return $this->serverRequest->getAttributes();
    }

    /**
     * Get the session
     */
    public function session(): ?Session
    {
        return $this->attribute('session');
    }

    /**
     * Get the underlying PSR-7 request
     */
    public function getPsrRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }
} 