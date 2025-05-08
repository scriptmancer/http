<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/ApiController.php';

use Nazim\Http\Examples\ApiController;
use Nazim\Http\Middleware\ContentTypeMiddleware;
use Nazim\Http\Middleware\MiddlewareInterface;
use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;
use Nazim\Http\Server\Server;

// Create a CORS middleware
class CorsMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // Process the request
        $response = $next($request);
        
        // Add CORS headers
        return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Add middleware
$server->addMiddleware(new CorsMiddleware());
$server->addMiddleware(new ContentTypeMiddleware('application/json'));

// Create controller
$controller = new ApiController();

// Handle the request using the controller
$response = $server->handle($request, function (Request $request) use ($controller) {
    return $controller->handle($request);
});

// Send the response
$server->send($response); 