# Scriptmancer HTTP

HTTP foundation layer for the Scriptmancer framework. This package provides a lightweight, modern, and PSR-7 compliant HTTP implementation.

## Requirements

- PHP 8.0 or higher
- Composer

## Installation

```bash
composer require scriptmancer/http
```

## Features

- **PSR-7 Compliant**: Implements PSR-7 HTTP message interfaces
- **Request Handling**: Process incoming HTTP requests with ease
- **Response Types**: Support for various response types (HTML, JSON, XML, files, downloads, streams)
- **Middleware System**: Flexible middleware architecture
- **Cookie Management**: Comprehensive cookie handling with support for all modern attributes
- **Session Management**: Robust session handling with flash messages
- **File Uploads**: Simple API for handling file uploads
- **HTTP Client**: Make API requests to external services
- **Server Component**: Process requests and send responses
- **Exception Handling**: HTTP-specific exceptions with proper status codes

## Basic Usage

### Creating a Simple Server

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;
use Scriptmancer\Http\Server\Server;

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Define a handler
$handler = function (Request $request) {
    return Response::html('<h1>Hello, World!</h1>');
};

// Handle the request and get the response
$response = $server->handle($request, $handler);

// Send the response
$server->send($response);
```

### Working with Requests

```php
// Get query parameters
$id = $request->query('id');
$page = $request->query('page', 1); // With default value

// Get post data
$name = $request->post('name');
$email = $request->post('email');

// Get headers
$userAgent = $request->header('User-Agent');

// Check request method
if ($request->isMethod('POST')) {
    // Handle POST request
}

// Get uploaded files
$file = $request->file('avatar');
```

### Working with Responses

```php
// HTML Response
$response = Response::html('<h1>Welcome</h1>');

// JSON Response
$response = Response::json(['name' => 'John', 'age' => 30]);

// Text Response
$response = Response::text('Plain text content');

// XML Response
$response = Response::xml('<root><item>Value</item></root>');

// File Download
$response = Response::download('/path/to/file.pdf', 'document.pdf');

// Inline File
$response = Response::inline('/path/to/image.jpg');

// Redirect
$response = Response::redirect('/dashboard');

// Status Codes
$response = Response::notFound('Page not found');
$response = Response::badRequest('Invalid input');
$response = Response::serverError('Something went wrong');
$response = Response::noContent();
$response = Response::created('/users/123', 'User created');
```

### Using Cookies

```php
// Add a cookie to the response
$response = $response->withSimpleCookie(
    'preferences', 
    'theme=dark', 
    86400 * 30, // 30 days
    '/',
    null,
    true,
    true,
    'Lax'
);

// Check if a cookie exists
if ($request->hasCookie('preferences')) {
    $preferences = $request->cookie('preferences');
}

// Remove a cookie
$response = $response->withExpiredCookie('preferences');
```

### Working with Sessions

```php
// Get the session middleware
$sessionMiddleware = new SessionMiddleware();
$server->addMiddleware($sessionMiddleware);

// In your request handler
$session = $request->session();

// Store data in the session
$session->set('user_id', 123);

// Get data from the session
$userId = $session->get('user_id');

// Remove data from the session
$session->remove('user_id');

// Flash messages (available only for the next request)
$session->flash('message', 'Operation successful');

// Get a flash message
$message = $session->getFlash('message');
```

### Using Middleware

```php
// Create a server with middleware
$server = new Server();

// Add middleware
$server->addMiddleware(new ContentTypeMiddleware());
$server->addMiddleware(new CorsMiddleware([
    'allowedOrigins' => ['https://example.com'],
    'allowedMethods' => ['GET', 'POST'],
]));
$server->addMiddleware(new SecurityHeadersMiddleware());
$server->addMiddleware(new SessionMiddleware());

// Handle request with middleware
$response = $server->handle($request, $handler);
```

### HTTP Client for Making API Requests

```php
use Scriptmancer\Http\Client\HttpClient;

// Create a client
$client = new HttpClient([
    'base_uri' => 'https://api.example.com',
    'timeout' => 10,
]);

// Make requests
$response = $client->get('/users');
$response = $client->post('/users', [
    'json' => ['name' => 'John', 'email' => 'john@example.com']
]);

// Handle response
$statusCode = $response->getStatusCode();
$data = json_decode($response->getBody(), true);
```

## Examples

Check the `examples` directory for more detailed examples:

- `simple-server.php`: Basic server setup
- `getting-started.php`: Quick introduction to the framework
- `cookie-example.php`: Working with cookies
- `session-example.php`: Session management
- `file-upload.php`: Handling file uploads
- `middleware-server.php`: Using middleware
- `http-client.php`: Making API requests
- `response-types.php`: All available response types

## License

This project is licensed under the MIT License. 