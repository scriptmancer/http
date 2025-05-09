<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Response;

use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\Psr7\Utils;
use Scriptmancer\Http\Cookie\Cookie;
use Scriptmancer\Http\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response
{
    private ResponseInterface $response;
    private CookieJar $cookieJar;

    public function __construct(
        string|StreamInterface $body = '',
        int $status = 200,
        array $headers = [],
        string $version = '1.1',
        ?string $reason = null
    ) {
        $this->response = new PsrResponse(
            $status,
            $headers,
            $body,
            $version,
            $reason
        );
        
        $this->cookieJar = new CookieJar();
    }

    /**
     * Create a new response from an existing PSR-7 response
     */
    public static function fromPsrResponse(ResponseInterface $response): self
    {
        $new = new self();
        $new->response = $response;
        return $new;
    }

    /**
     * Get the response status code
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Create a new response with a different status code
     */
    public function withStatus(int $code, ?string $reasonPhrase = null): self
    {
        $new = clone $this;
        $new->response = $this->response->withStatus($code, $reasonPhrase);
        return $new;
    }

    /**
     * Get a response header
     */
    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    /**
     * Get all response headers
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Create a new response with an additional header
     */
    public function withHeader(string $name, string|array $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withHeader($name, $value);
        return $new;
    }

    /**
     * Create a new response with an appended header
     */
    public function withAddedHeader(string $name, string|array $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withAddedHeader($name, $value);
        return $new;
    }

    /**
     * Create a new response without a header
     */
    public function withoutHeader(string $name): self
    {
        $new = clone $this;
        $new->response = $this->response->withoutHeader($name);
        return $new;
    }

    /**
     * Get the response body as a string
     */
    public function getBody(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get the response body as a stream
     */
    public function getBodyStream(): StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     * Create a new response with a different body
     */
    public function withBody(string|StreamInterface $body): self
    {
        $new = clone $this;
        if (is_string($body)) {
            $stream = Utils::streamFor($body);
            $new->response = $this->response->withBody($stream);
        } else {
            $new->response = $this->response->withBody($body);
        }
        return $new;
    }

    /**
     * Get the cookie jar
     */
    public function getCookieJar(): CookieJar
    {
        return $this->cookieJar;
    }

    /**
     * Get a cookie by name
     */
    public function getCookie(string $name): ?Cookie
    {
        return $this->cookieJar->get($name);
    }

    /**
     * Check if a cookie exists
     */
    public function hasCookie(string $name): bool
    {
        return $this->cookieJar->has($name);
    }

    /**
     * Add a cookie to the response
     */
    public function withCookie(Cookie $cookie): self
    {
        $new = clone $this;
        $new->cookieJar->set($cookie);
        return $new;
    }

    /**
     * Remove a cookie from the response
     */
    public function withoutCookie(string $name): self
    {
        $new = clone $this;
        $new->cookieJar->remove($name);
        return $new;
    }

    /**
     * Create a cookie and add it to the response
     */
    public function withSimpleCookie(
        string $name,
        string $value,
        int $maxAge = 0,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        ?string $sameSite = 'Lax'
    ): self {
        return $this->withCookie(new Cookie($name, $value, $maxAge, $path, $domain, $secure, $httpOnly, $sameSite));
    }

    /**
     * Add an expired cookie to make the browser delete it
     */
    public function withExpiredCookie(string $name, string $path = '/', ?string $domain = null): self
    {
        return $this->withCookie(Cookie::createExpired($name, $path, $domain));
    }

    /**
     * Get the underlying PSR-7 response
     */
    public function getPsrResponse(): ResponseInterface
    {
        // Add cookies as headers before returning the PSR response
        $response = $this->response;
        
        foreach ($this->cookieJar->toResponseHeaders() as $cookie) {
            $response = $response->withAddedHeader('Set-Cookie', $cookie);
        }
        
        return $response;
    }

    /**
     * Create a JSON response
     */
    public static function json(
        mixed $data,
        int $status = 200,
        array $headers = [],
        int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ): self {
        $json = json_encode($data, $options);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode data as JSON');
        }

        return new self(
            $json,
            $status,
            array_merge(['Content-Type' => 'application/json'], $headers)
        );
    }

    /**
     * Create an HTML response
     */
    public static function html(
        string $html,
        int $status = 200,
        array $headers = []
    ): self {
        return new self(
            $html,
            $status,
            array_merge(['Content-Type' => 'text/html; charset=UTF-8'], $headers)
        );
    }

    /**
     * Create a plain text response
     */
    public static function text(
        string $text,
        int $status = 200,
        array $headers = []
    ): self {
        return new self(
            $text,
            $status,
            array_merge(['Content-Type' => 'text/plain; charset=UTF-8'], $headers)
        );
    }

    /**
     * Create an XML response
     */
    public static function xml(
        string $xml,
        int $status = 200,
        array $headers = []
    ): self {
        return new self(
            $xml,
            $status,
            array_merge(['Content-Type' => 'application/xml; charset=UTF-8'], $headers)
        );
    }

    /**
     * Create a file download response
     */
    public static function download(
        string $filePath,
        ?string $filename = null,
        array $headers = []
    ): self {
        // Check if file exists
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("File {$filePath} does not exist or is not readable");
        }

        // Get filename if not provided
        $filename = $filename ?? basename($filePath);

        // Get file size and MIME type
        $filesize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Create file stream
        $stream = Utils::streamFor(fopen($filePath, 'r'));

        // Set appropriate headers
        $downloadHeaders = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => (string) $filesize,
            'Cache-Control' => 'no-cache, private',
        ];

        return new self(
            $stream,
            200,
            array_merge($downloadHeaders, $headers)
        );
    }

    /**
     * Create an inline content response for displaying files in browser
     */
    public static function inline(
        string $filePath,
        ?string $filename = null,
        array $headers = []
    ): self {
        // Check if file exists
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("File {$filePath} does not exist or is not readable");
        }

        // Get filename if not provided
        $filename = $filename ?? basename($filePath);

        // Get file size and MIME type
        $filesize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Create file stream
        $stream = Utils::streamFor(fopen($filePath, 'r'));

        // Set appropriate headers
        $inlineHeaders = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Content-Length' => (string) $filesize,
            'Cache-Control' => 'public, max-age=86400',
        ];

        return new self(
            $stream,
            200,
            array_merge($inlineHeaders, $headers)
        );
    }

    /**
     * Create a stream response
     */
    public static function stream(
        callable $callback,
        int $status = 200,
        array $headers = []
    ): self {
        // Create a custom stream with all required methods
        $stream = new \GuzzleHttp\Psr7\FnStream([
            'eof' => function () {
                return false;
            },
            'isSeekable' => function () {
                return false; // Streaming response is not seekable
            },
            'rewind' => function () {
                return null; // No-op as it's not seekable
            },
            'getSize' => function () {
                return null; // Size is unknown for streaming
            },
            'tell' => function () {
                throw new \RuntimeException('Streaming response does not support tell');
            },
            'seek' => function () {
                throw new \RuntimeException('Streaming response does not support seek');
            },
            'read' => function ($length) use ($callback) {
                return $callback($length);
            },
            '__toString' => function () {
                return ''; // Return empty string when cast to string
            }
        ]);

        // Default headers for streaming
        $streamHeaders = [
            'Cache-Control' => 'no-cache, private',
            'Transfer-Encoding' => 'chunked',
            'Content-Type' => 'text/plain',
        ];

        return new self(
            $stream,
            $status,
            array_merge($streamHeaders, $headers)
        );
    }

    /**
     * Create a response with a file's contents
     */
    public static function file(
        string $filePath,
        array $headers = []
    ): self {
        // Check if file exists
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("File {$filePath} does not exist or is not readable");
        }

        // Get file MIME type
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Create file stream
        $stream = Utils::streamFor(fopen($filePath, 'r'));

        return new self(
            $stream,
            200,
            array_merge(['Content-Type' => $mimeType], $headers)
        );
    }

    /**
     * Create a redirect response
     */
    public static function redirect(string $url, int $status = 302, array $headers = []): self
    {
        return new self('', $status, array_merge(['Location' => $url], $headers));
    }

    /**
     * Create a permanent redirect response (301)
     */
    public static function permanentRedirect(string $url, array $headers = []): self
    {
        return self::redirect($url, 301, $headers);
    }

    /**
     * Create a "Found" redirect response (302)
     */
    public static function found(string $url, array $headers = []): self
    {
        return self::redirect($url, 302, $headers);
    }

    /**
     * Create a "See Other" redirect response (303)
     */
    public static function seeOther(string $url, array $headers = []): self
    {
        return self::redirect($url, 303, $headers);
    }

    /**
     * Create a "Temporary Redirect" response (307)
     */
    public static function temporaryRedirect(string $url, array $headers = []): self
    {
        return self::redirect($url, 307, $headers);
    }

    /**
     * Create an empty response with No Content status (204)
     */
    public static function noContent(array $headers = []): self
    {
        return new self('', 204, $headers);
    }

    /**
     * Create a response with status "OK" (200)
     */
    public static function ok(string $content = '', array $headers = []): self
    {
        return new self($content, 200, $headers);
    }

    /**
     * Create a response with status "Created" (201)
     */
    public static function created(string $location = '', string $content = '', array $headers = []): self
    {
        $headers = $location ? array_merge(['Location' => $location], $headers) : $headers;
        return new self($content, 201, $headers);
    }

    /**
     * Create a response with status "Accepted" (202)
     */
    public static function accepted(string $content = '', array $headers = []): self
    {
        return new self($content, 202, $headers);
    }

    /**
     * Create a response with status "Bad Request" (400)
     */
    public static function badRequest(string $content = 'Bad Request', array $headers = []): self
    {
        return new self($content, 400, $headers);
    }

    /**
     * Create a response with status "Unauthorized" (401)
     */
    public static function unauthorized(string $content = 'Unauthorized', array $headers = []): self
    {
        return new self($content, 401, $headers);
    }

    /**
     * Create a response with status "Forbidden" (403)
     */
    public static function forbidden(string $content = 'Forbidden', array $headers = []): self
    {
        return new self($content, 403, $headers);
    }

    /**
     * Create a response with status "Not Found" (404)
     */
    public static function notFound(string $content = 'Not Found', array $headers = []): self
    {
        return new self($content, 404, $headers);
    }

    /**
     * Create a response with status "Method Not Allowed" (405)
     */
    public static function methodNotAllowed(
        array $allowedMethods = [], 
        string $content = 'Method Not Allowed', 
        array $headers = []
    ): self {
        if (!empty($allowedMethods)) {
            $headers = array_merge(['Allow' => implode(', ', $allowedMethods)], $headers);
        }
        return new self($content, 405, $headers);
    }

    /**
     * Create a response with status "Internal Server Error" (500)
     */
    public static function serverError(string $content = 'Internal Server Error', array $headers = []): self
    {
        return new self($content, 500, $headers);
    }

    /**
     * Convert the response to a string (for debugging or logging)
     */
    public function __toString(): string
    {
        $response = $this->getPsrResponse();
        $output = sprintf(
            "HTTP/%s %d %s\r\n",
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        
        foreach ($response->getHeaders() as $name => $values) {
            $output .= sprintf("%s: %s\r\n", $name, implode(', ', $values));
        }
        
        $output .= "\r\n";
        $body = (string) $response->getBody();
        
        // If body is very large, truncate it for the string representation
        if (strlen($body) > 1024) {
            $body = substr($body, 0, 1024) . '... (truncated)';
        }
        
        $output .= $body;
        
        return $output;
    }
} 