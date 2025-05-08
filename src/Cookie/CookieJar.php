<?php

declare(strict_types=1);

namespace Nazim\Http\Cookie;

use Countable;
use Iterator;

class CookieJar implements Countable, Iterator
{
    /**
     * @var array<string, Cookie>
     */
    private array $cookies = [];

    /**
     * @var int
     */
    private int $position = 0;

    /**
     * @var array<string>
     */
    private array $keys = [];

    /**
     * Constructor
     *
     * @param array<Cookie> $cookies
     */
    public function __construct(array $cookies = [])
    {
        foreach ($cookies as $cookie) {
            $this->set($cookie);
        }
    }

    /**
     * Add a cookie to the jar
     *
     * @param Cookie $cookie
     * @return self
     */
    public function set(Cookie $cookie): self
    {
        $this->cookies[$cookie->getName()] = $cookie;
        $this->keys = array_keys($this->cookies);
        return $this;
    }

    /**
     * Get a cookie by name
     *
     * @param string $name
     * @return Cookie|null
     */
    public function get(string $name): ?Cookie
    {
        return $this->cookies[$name] ?? null;
    }

    /**
     * Check if a cookie exists
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Remove a cookie by name
     *
     * @param string $name
     * @return self
     */
    public function remove(string $name): self
    {
        unset($this->cookies[$name]);
        $this->keys = array_keys($this->cookies);
        return $this;
    }

    /**
     * Get all cookies
     *
     * @return array<string, Cookie>
     */
    public function all(): array
    {
        return $this->cookies;
    }

    /**
     * Clear all cookies
     *
     * @return self
     */
    public function clear(): self
    {
        $this->cookies = [];
        $this->keys = [];
        return $this;
    }

    /**
     * Get the number of cookies
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->cookies);
    }

    /**
     * Get the current cookie
     *
     * @return Cookie
     */
    public function current(): Cookie
    {
        $key = $this->keys[$this->position];
        return $this->cookies[$key];
    }

    /**
     * Get the key of the current cookie
     *
     * @return string|null
     */
    public function key(): ?string
    {
        return $this->keys[$this->position] ?? null;
    }

    /**
     * Move to the next cookie
     *
     * @return void
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Rewind the iterator
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Check if the current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }

    /**
     * Parse cookies from request headers
     *
     * @param array $headers Cookie headers from the request
     * @return self
     */
    public static function fromRequestHeaders(array $headers): self
    {
        $jar = new self();
        
        foreach ($headers as $header) {
            $cookies = explode('; ', $header);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie, 2);
                if (count($parts) === 2) {
                    $name = trim($parts[0]);
                    $value = urldecode(trim($parts[1]));
                    $jar->set(new Cookie($name, $value));
                }
            }
        }
        
        return $jar;
    }

    /**
     * Get the cookie headers for the response
     *
     * @return array<string>
     */
    public function toResponseHeaders(): array
    {
        $headers = [];
        
        foreach ($this->cookies as $cookie) {
            $headers[] = $cookie->toHeaderString();
        }
        
        return $headers;
    }
} 