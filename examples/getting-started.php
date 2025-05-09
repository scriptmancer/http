<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;
use Scriptmancer\Http\Server\Server;

// Step 1: Create a request from globals
$request = Request::fromGlobals();

// Step 2: Create a server
$server = new Server();

// Step 3: Define a handler
$handler = function (Request $request) {
    // Get the name from the query parameters or use 'World' as default
    $name = $request->query('name', 'World');
    
    // Get the format from the query parameters or use 'html' as default
    $format = strtolower($request->query('format', 'html'));
    
    // Prepare the message
    $message = "Hello, {$name}!";
    
    // Return the response based on the requested format
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
    
    // Default to HTML
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Nazim HTTP - Getting Started</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .message {
            font-size: 24px;
            margin: 30px 0;
        }
        .info {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 30px;
            text-align: left;
        }
        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Nazim HTTP Framework</h1>
    <div class="message">{$message}</div>
    
    <div class="info">
        <p>This is a simple example of the Nazim HTTP framework.</p>
        <p>Try these different formats:</p>
        <ul>
            <li><a href="?format=html&name={$name}">HTML Format</a></li>
            <li><a href="?format=json&name={$name}">JSON Format</a></li>
            <li><a href="?format=text&name={$name}">Plain Text Format</a></li>
        </ul>
        <p>Or change the name parameter:</p>
        <ul>
            <li><a href="?name=Nazim&format={$format}">name=Nazim</a></li>
            <li><a href="?name=PHP&format={$format}">name=PHP</a></li>
        </ul>
    </div>
</body>
</html>
HTML;

    return new Response($html, 200, [
        'Content-Type' => 'text/html'
    ]);
};

// Step 4: Handle the request and get the response
$response = $server->handle($request, $handler);

// Step 5: Send the response
$server->send($response); 