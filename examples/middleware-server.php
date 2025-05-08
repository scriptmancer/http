<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Nazim\Http\Middleware\ContentTypeMiddleware;
use Nazim\Http\Middleware\MiddlewareInterface;
use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;
use Nazim\Http\Server\Server;

// Create a custom middleware
class LoggingMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // Log before processing
        echo "[" . date('Y-m-d H:i:s') . "] Processing request: " . $request->method() . " " . $request->uri() . PHP_EOL;
        
        // Call the next middleware
        $response = $next($request);
        
        // Log after processing
        echo "[" . date('Y-m-d H:i:s') . "] Response status: " . $response->getStatusCode() . PHP_EOL;
        
        return $response;
    }
}

// Create another custom middleware
class TimerMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // Record start time
        $start = microtime(true);
        
        // Call the next middleware
        $response = $next($request);
        
        // Calculate execution time
        $executionTime = microtime(true) - $start;
        
        // Add execution time as a header
        return $response->withHeader('X-Execution-Time', round($executionTime * 1000, 2) . 'ms');
    }
}

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Add middleware to the stack
$server->addMiddleware(new LoggingMiddleware());
$server->addMiddleware(new TimerMiddleware());
$server->addMiddleware(new ContentTypeMiddleware('application/json'));

// Define the handler
$handler = function (Request $request) {
    // Get query parameter
    $name = $request->query('name', 'World');
    
    // Return a JSON response
    return Response::json([
        'message' => "Hello, {$name}!",
        'timestamp' => time()
    ]);
};

// Handle the request and get the response
$response = $server->handle($request, $handler);

// Send the response
$server->send($response); 