<?php

declare(strict_types=1);

namespace Nazim\Http\Cookie;

class Cookie
{
    /**
     * @var string
     */
    private string $name;
    
    /**
     * @var string
     */
    private string $value;
    
    /**
     * @var int
     */
    private int $maxAge;
    
    /**
     * @var string|null
     */
    private ?string $path;
    
    /**
     * @var string|null
     */
    private ?string $domain;
    
    /**
     * @var bool
     */
    private bool $secure;
    
    /**
     * @var bool
     */
    private bool $httpOnly;
    
    /**
     * @var string|null
     */
    private ?string $sameSite;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $value
     * @param int $maxAge
     * @param string|null $path
     * @param string|null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param string|null $sameSite
     */
    public function __construct(
        string $name,
        string $value = '',
        int $maxAge = 0,
        ?string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        ?string $sameSite = 'Lax'
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->maxAge = $maxAge;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the max age
     *
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * Get the path
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Get the domain
     *
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Whether the cookie is secure
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Whether the cookie is HTTP only
     *
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * Get the SameSite attribute
     *
     * @return string|null
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }

    /**
     * Get the cookie string for HTTP headers
     *
     * @return string
     */
    public function toHeaderString(): string
    {
        $parts = [
            $this->name . '=' . urlencode($this->value),
        ];

        if ($this->maxAge !== 0) {
            $parts[] = 'Max-Age=' . $this->maxAge;
            // Include Expires for compatibility with older browsers
            $parts[] = 'Expires=' . gmdate('D, d M Y H:i:s T', time() + $this->maxAge);
        }

        if ($this->domain) {
            $parts[] = 'Domain=' . $this->domain;
        }

        if ($this->path) {
            $parts[] = 'Path=' . $this->path;
        }

        if ($this->secure) {
            $parts[] = 'Secure';
        }

        if ($this->httpOnly) {
            $parts[] = 'HttpOnly';
        }

        if ($this->sameSite) {
            $parts[] = 'SameSite=' . $this->sameSite;
        }

        return implode('; ', $parts);
    }

    /**
     * Create a cookie from a string
     *
     * @param string $cookieString
     * @return static
     */
    public static function fromString(string $cookieString): self
    {
        $parts = explode(';', $cookieString);
        $nameValue = explode('=', trim($parts[0]), 2);

        $name = $nameValue[0];
        $value = isset($nameValue[1]) ? urldecode($nameValue[1]) : '';

        $maxAge = 0;
        $path = '/';
        $domain = null;
        $secure = false;
        $httpOnly = true;
        $sameSite = 'Lax';

        for ($i = 1; $i < count($parts); $i++) {
            $part = trim($parts[$i]);
            if (strpos($part, '=') !== false) {
                [$key, $val] = explode('=', $part, 2);
                $key = trim($key);
                $val = trim($val);

                if (strtolower($key) === 'max-age') {
                    $maxAge = (int) $val;
                } elseif (strtolower($key) === 'path') {
                    $path = $val;
                } elseif (strtolower($key) === 'domain') {
                    $domain = $val;
                } elseif (strtolower($key) === 'samesite') {
                    $sameSite = $val;
                }
            } else {
                if (strtolower($part) === 'secure') {
                    $secure = true;
                } elseif (strtolower($part) === 'httponly') {
                    $httpOnly = true;
                }
            }
        }

        return new self($name, $value, $maxAge, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    /**
     * Create an expired cookie
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return static
     */
    public static function createExpired(string $name, ?string $path = '/', ?string $domain = null): self
    {
        return new self($name, '', -1, $path, $domain);
    }
} 