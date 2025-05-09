<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;
use Scriptmancer\Http\Server\Server;

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Define the handler
$handler = function (Request $request) {
    // Get query parameter
    $name = $request->query('name', 'World');

    // Return a response based on the request method
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
    
    // For other methods, return a 405 Method Not Allowed
    return new Response('Method Not Allowed', 405, [
        'Allow' => 'GET, POST'
    ]);
};

// Handle the request and get the response
$response = $server->handle($request, $handler);

// Send the response
$server->send($response); 