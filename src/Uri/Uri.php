<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Uri;

use GuzzleHttp\Psr7\Uri as PsrUri;
use Psr\Http\Message\UriInterface;

class Uri
{
    private UriInterface $uri;

    /**
     * Create a new URI
     */
    public function __construct(string|UriInterface $uri = '')
    {
        $this->uri = is_string($uri) ? new PsrUri($uri) : $uri;
    }

    /**
     * Create a new instance from the current URI with the specified scheme
     */
    public function withScheme(string $scheme): self
    {
        $clone = clone $this;
        $clone->uri = $this->uri->withScheme($scheme);
        return $clone;
    }

    /**
     * Create a new instance from the current URI with the specified user info
     */
    public function withUserInfo(string $user, ?string $password = null): self
    {
        $clone = clone $this;
        $clone->uri = $this->uri->withUserInfo($user, $password);
        return $clone;
    }

    /**
     * Create a new instance from the current URI with the specified host
     */
    public function withHost(string $host): self
    {
        $clone = clone $this;
        $clone->uri = $this->uri->withHost($host);
        return $clone;
    }

    /**
     * Create a new instance from the current URI with the specified port
     */
    public function withPort(?int $port): self
    {
        $clone = clone $this;
        $clone->uri = $this->uri->withPort($port);
        return $clone;
    }

    /**
     * Create a new instance from the current URI with the specified path
     */
    public function withPath(string $path): self
    {
        $clone = clone $this;
        $clone->uri = $this->uri->withPath($path);
        return $clone;
    }

    /**
     * Create a new instance from the current URI with the specified query string
     */
    public function withQuery(string $query): self
    {
        $clone = clone $this;
        $clone->uri = $this->uri->withQuery($query);
        return $clone;
    }

    /**
     * Create a new instance from the current URI with the specified fragment
     */
    public function withFragment(string $fragment): self
    {
        $clone = clone $this;
        $clone->uri = $this->uri->withFragment($fragment);
        return $clone;
    }

    /**
     * Return the string representation of the URI
     */
    public function __toString(): string
    {
        return (string) $this->uri;
    }

    /**
     * Get the scheme component of the URI
     */
    public function getScheme(): string
    {
        return $this->uri->getScheme();
    }

    /**
     * Get the authority component of the URI
     */
    public function getAuthority(): string
    {
        return $this->uri->getAuthority();
    }

    /**
     * Get the user information component of the URI
     */
    public function getUserInfo(): string
    {
        return $this->uri->getUserInfo();
    }

    /**
     * Get the host component of the URI
     */
    public function getHost(): string
    {
        return $this->uri->getHost();
    }

    /**
     * Get the port component of the URI
     */
    public function getPort(): ?int
    {
        return $this->uri->getPort();
    }

    /**
     * Get the path component of the URI
     */
    public function getPath(): string
    {
        return $this->uri->getPath();
    }

    /**
     * Get the query string component of the URI
     */
    public function getQuery(): string
    {
        return $this->uri->getQuery();
    }

    /**
     * Get the fragment component of the URI
     */
    public function getFragment(): string
    {
        return $this->uri->getFragment();
    }

    /**
     * Get an associative array of query parameters
     */
    public function getQueryParams(): array
    {
        $query = $this->getQuery();
        if (empty($query)) {
            return [];
        }

        $params = [];
        parse_str($query, $params);
        return $params;
    }

    /**
     * Get the underlying PSR-7 URI
     */
    public function getPsrUri(): UriInterface
    {
        return $this->uri;
    }
} 