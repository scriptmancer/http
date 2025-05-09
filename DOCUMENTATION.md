# Scriptmancer HTTP Package Documentation

## Index

- [Getting Started](#getting-started) — See: `examples/getting-started.php`
- [Simple HTTP Server](#simple-http-server) — See: `examples/simple-server.php`
- [Controllers](#controllers) — See: `examples/controller-example.php`, `examples/ApiController.php`
- [Middleware Usage](#middleware-usage) — See: `examples/middleware-server.php`
- [Response Types](#response-types) — See: `examples/response-types.php`
- [Cookie Management](#cookie-management) — See: `examples/cookie-example.php`
- [Session Management](#session-management) — See: `examples/session-example.php`
- [File Uploads](#file-uploads) — See: `examples/file-upload.php`
- [HTTP Client](#http-client) — See: `examples/http-client.php`

---

## Getting Started

This example demonstrates how to set up a basic HTTP server using Scriptmancer HTTP. It shows how to create a request from globals, instantiate a server, define a handler, and return responses in different formats (HTML, JSON, plain text) based on query parameters.

**File:** [`examples/getting-started.php`](examples/getting-started.php)

```php
$request = Request::fromGlobals();
$server = new Server();
$handler = function (Request $request) {
    $name = $request->query('name', 'World');
    $format = strtolower($request->query('format', 'html'));
    $message = "Hello, {$name}!";
    if ($format === 'json') {
        return Response::json([
            'message' => $message,
            'timestamp' => time()
        ]);
    }
    if ($format === 'text') {
        return new Response($message, 200, [
            'Content-Type' => 'text/plain'
        ]);
    }
    // Default to HTML...
};
$response = $server->handle($request, $handler);
$server->send($response);
```

**Explanation:**
- Handles different response types based on the `format` query parameter.
- Demonstrates the core flow: request creation, server instantiation, handler definition, and response sending.

---

## Simple HTTP Server

This example shows how to build a minimal HTTP server that responds to GET and POST requests with different content types and appropriate status codes.

**File:** [`examples/simple-server.php`](examples/simple-server.php)

```php
$request = Request::fromGlobals();
$server = new Server();
$handler = function (Request $request) {
    $name = $request->query('name', 'World');
    if ($request->isMethod('GET')) {
        return new Response("Hello, {$name}!", 200, [
            'Content-Type' => 'text/plain'
        ]);
    }
    if ($request->isMethod('POST')) {
        $data = $request->postParams();
        return Response::json([
            'message' => "Hello, {$name}!",
            'data' => $data
        ]);
    }
    return new Response('Method Not Allowed', 405, [
        'Allow' => 'GET, POST'
    ]);
};
$response = $server->handle($request, $handler);
$server->send($response);
```

**Explanation:**
- Responds to GET with plain text and POST with JSON.
- Returns a 405 error for unsupported methods.

---

## Controllers

This section covers using controllers for routing and logic separation. The example includes a custom API controller and shows how to add CORS and content-type middleware.

**Files:** [`examples/controller-example.php`](examples/controller-example.php), [`examples/ApiController.php`](examples/ApiController.php)

```php
// In controller-example.php
class CorsMiddleware implements MiddlewareInterface {
    public function process(Request $request, callable $next): Response {
        $response = $next($request);
        return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
$server->addMiddleware(new CorsMiddleware());
$server->addMiddleware(new ContentTypeMiddleware('application/json'));
$controller = new ApiController();
```

```php
// In ApiController.php
class ApiController implements ControllerInterface {
    public function handle(Request $request): Response {
        $method = $request->method();
        return match ($method) {
            'GET' => $this->handleGet($request),
            'POST' => $this->handlePost($request),
            'PUT', 'PATCH' => $this->handlePut($request),
            'DELETE' => $this->handleDelete($request),
            default => throw new BadRequestException("Method {$method} not supported")
        };
    }
    // ...
}
```

**Explanation:**
- Shows how to use middleware and controllers for clean, modular logic.
- Demonstrates RESTful method dispatching in the controller.

---

## Middleware Usage

Demonstrates how to create and use custom middleware, such as logging and timing, and how to integrate them into the server pipeline.

**File:** [`examples/middleware-server.php`](examples/middleware-server.php)

```php
class LoggingMiddleware implements MiddlewareInterface {
    public function process(Request $request, callable $next): Response {
        echo "[" . date('Y-m-d H:i:s') . "] Processing request: " . $request->method() . " " . $request->uri() . PHP_EOL;
        $response = $next($request);
        echo "[" . date('Y-m-d H:i:s') . "] Response status: " . $response->getStatusCode() . PHP_EOL;
        return $response;
    }
}
class TimerMiddleware implements MiddlewareInterface {
    public function process(Request $request, callable $next): Response {
        $start = microtime(true);
        $response = $next($request);
        $duration = microtime(true) - $start;
        echo "Request processed in {$duration} seconds\n";
        return $response;
    }
}
$server->addMiddleware(new LoggingMiddleware());
$server->addMiddleware(new TimerMiddleware());
```

**Explanation:**
- Middleware can perform actions before and after the request is processed.
- Middleware is added to the server pipeline for cross-cutting concerns.

---

## Advanced Middleware

### MiddlewareStack

The `MiddlewareStack` class manages and executes a sequence of middleware components. It allows you to compose middleware and control their execution order.

**Example:**
```php
use Scriptmancer\Http\Middleware\MiddlewareStack;

$stack = new MiddlewareStack();
$stack->add(new LoggingMiddleware());
$stack->add(new TimerMiddleware());

$response = $stack->process($request, $handler);
```

**Explanation:**
- Add middleware in the order you want them executed.
- `process()` runs the middleware chain and finally calls the handler.

### SessionMiddleware

`SessionMiddleware` initializes a session and attaches it to the request. Useful for persisting user data across requests.

**Example:**
```php
use Scriptmancer\Http\Middleware\SessionMiddleware;

$server->addMiddleware(new SessionMiddleware([
    'name' => 'MYSESSID',
    'cookie_secure' => true
]));
```

**Explanation:**
- Starts a session and makes it available via `$request->session()`.
- Configurable with options like session name and cookie settings.

### SecurityHeadersMiddleware

Adds common security-related headers to every response (e.g., Content-Security-Policy, X-Frame-Options).

**Example:**
```php
use Scriptmancer\Http\Middleware\SecurityHeadersMiddleware;

$server->addMiddleware(new SecurityHeadersMiddleware([
    'Content-Security-Policy' => "default-src 'self'",
    'X-Frame-Options' => 'DENY',
]));
```

**Explanation:**
- Helps protect against common web vulnerabilities by setting headers.
- Pass an array of headers to customize.

### RateLimitMiddleware

Limits the number of requests a client can make in a given time window. Useful for API protection.

**Example:**
```php
use Scriptmancer\Http\Middleware\RateLimitMiddleware;

$server->addMiddleware(new RateLimitMiddleware([
    'limit' => 100,
    'window' => 60, // seconds
]));
```

**Explanation:**
- Returns a 429 Too Many Requests response if the limit is exceeded.
- Configurable limit and window.

### CacheMiddleware

Caches responses for faster repeated access to the same resources.

**Example:**
```php
use Scriptmancer\Http\Middleware\CacheMiddleware;

$server->addMiddleware(new CacheMiddleware([
    'ttl' => 300, // cache time-to-live in seconds
]));
```

**Explanation:**
- Stores responses and serves them from cache if available.
- Useful for GET endpoints and static content.


## Overview

Scriptmancer HTTP provides a lightweight, modern, and PSR-7 compliant HTTP foundation for PHP applications and the Scriptmancer framework. It offers an extensible set of classes for handling HTTP requests, responses, middleware, cookies, sessions, file uploads, and more.

## Features

- PSR-7 Compliant HTTP messages
- Flexible middleware system
- Request and response handling
- Cookie and session management
- File upload API
- HTTP client for external requests
- Exception handling
- Extensible and framework-ready

## Requirements

- PHP 8.0 or higher
- Composer

## Installation

```bash
composer require scriptmancer/http
```

## Basic Usage

### Creating a Simple Server

```php
require_once __DIR__ . '/vendor/autoload.php';

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;
use Scriptmancer\Http\Server\Server;

$request = Request::fromGlobals();
$server = new Server();

$handler = function (Request $request) {
    return Response::html('<h1>Hello, World!</h1>');
};

$response = $server->handle($request, $handler);
$server->send($response);
```

### Using Middleware

```php
use Scriptmancer\Http\Middleware\ContentTypeMiddleware;

$middleware = new ContentTypeMiddleware('application/json');
$server->addMiddleware($middleware);
```

## Key Concepts

### Request
Represents an HTTP request. Implements PSR-7 interfaces. Create from globals or manually.

```php
$request = Request::fromGlobals();
```

### Response
Represents an HTTP response. Supports various response types:

```php
return Response::json(['status' => 'ok']);
return Response::html('<h1>Hello</h1>');
return Response::download('/path/to/file.zip');
```

### Middleware
Middleware can inspect or modify requests/responses as they pass through the stack.

#### Example: ContentTypeMiddleware

```php
use Scriptmancer\Http\Middleware\ContentTypeMiddleware;

$middleware = new ContentTypeMiddleware('application/json');
$server->addMiddleware($middleware);
```

#### ContentTypeMiddleware Source (with phpdoc):

```php
namespace Scriptmancer\Http\Middleware;

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

/**
 * Middleware to set the Content-Type header on the response.
 */
class ContentTypeMiddleware implements MiddlewareInterface
{
    /**
     * @var string Content-Type value to set
     */
    private string $contentType;

    /**
     * Constructor
     *
     * @param string $contentType Content-Type value (default: application/json)
     */
    public function __construct(string $contentType = 'application/json')
    {
        $this->contentType = $contentType;
    }

    /**
     * Process the request and add a content type header to the response
     *
     * @param Request $request HTTP request
     * @param callable $next Next middleware/controller
     * @return Response Modified response with Content-Type header
     */
    public function process(Request $request, callable $next): Response
    {
        $response = $next($request);
        return $response->withHeader('Content-Type', $this->contentType);
    }
}
```

## API Reference

### Request
- `fromGlobals()`: Create a request from PHP globals.
- Methods for accessing query, post, headers, cookies, files, etc.

### Response
- `html(string $html)`: Create an HTML response.
- `json(mixed $data)`: Create a JSON response.
- `withHeader(string $name, string $value)`: Return a copy with a header set.

### Server
- `handle(Request $request, callable $handler)`: Process an incoming request.
- `send(Response $response)`: Send the response to the client.

### MiddlewareInterface
- `process(Request $request, callable $next): Response`

---

## Additional Utilities & Advanced Usage

### Stream
The `Stream` class provides an easy way to work with PSR-7 streams, files, and memory buffers.

**Example:**
```php
use Scriptmancer\Http\Stream\Stream;

// Create a stream from a string
$stream = new Stream('Hello, world!');
echo $stream; // "Hello, world!"

// Create a stream from a file
$fileStream = Stream::fromFile('/path/to/file.txt', 'r');
echo $fileStream->getContents();

// Get the underlying PSR-7 stream
$psrStream = $stream->getPsrStream();
```

### Uri
The `Uri` class wraps and extends PSR-7 UriInterface for easy URI manipulation.

**Example:**
```php
use Scriptmancer\Http\Uri\Uri;

$uri = new Uri('https://example.com/path?foo=bar');
$newUri = $uri->withScheme('http')->withPath('/new');
$params = $uri->getQueryParams(); // ['foo' => 'bar']
```

### JsonSerializer
The `JsonSerializer` utility provides robust JSON encoding/decoding with exception handling.

**Example:**
```php
use Scriptmancer\Http\Util\JsonSerializer;

// Encode data to JSON
$json = JsonSerializer::encode(['foo' => 'bar']);

// Decode JSON to array
$data = JsonSerializer::decode($json);

// Handle errors
try {
    $broken = JsonSerializer::decode('invalid json');
} catch (JsonException $e) {
    // Handle error
}
```

### CookieJar
`CookieJar` helps manage multiple cookies for requests and responses.

**Example:**
```php
use Scriptmancer\Http\Cookie\CookieJar;
use Scriptmancer\Http\Cookie\Cookie;

$jar = new CookieJar();
$jar->set(new Cookie('session', 'abc123'));
$cookie = $jar->get('session');
$cookies = $jar->all(); // Returns all cookies as Cookie objects
```

### Exception Handling
Scriptmancer HTTP provides custom exceptions for HTTP error handling. Use them to signal specific HTTP errors in your controllers or middleware.

**Example:**
```php
use Scriptmancer\Http\Exception\BadRequestException;
use Scriptmancer\Http\Exception\NotFoundException;

if (!$request->query('id')) {
    throw new BadRequestException('Missing id parameter');
}

// ...
throw new NotFoundException('Resource not found');
```

**Explanation:**
- Throwing these exceptions will result in appropriate HTTP error responses (e.g., 400, 404) if handled in your error middleware or server logic.

---

## Examples Directory

- `getting-started.php`: Quick introduction
- `cookie-example.php`: Working with cookies
- `session-example.php`: Session management
- `file-upload.php`: Handling file uploads
- `middleware-server.php`: Using middleware

## Contribution

Contributions are welcome! Please submit issues or pull requests.

## License

MIT License. See composer.json for details.
