<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Scriptmancer\Http\Cookie\Cookie;
use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;
use Scriptmancer\Http\Server\Server;

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Define a handler
$handler = function (Request $request) {
    // Get the action from the query parameters
    $action = $request->query('action', 'view');
    
    // Get the name from the cookie or use 'Guest' as default
    $name = $request->cookie('name', 'Guest');
    
    // Handle different actions
    if ($action === 'set') {
        // Get the name from the query parameters
        $newName = $request->query('name', 'Guest');
        
        // Create a response with a cookie
        $response = new Response(
            "Cookie set! Your name is now: {$newName}",
            200,
            ['Content-Type' => 'text/html']
        );
        
        // Add the cookie to the response (expires in 30 days)
        return $response->withSimpleCookie(
            'name', 
            $newName, 
            86400 * 30, // 30 days
            '/',
            null,
            false,
            true,
            'Lax'
        );
    } elseif ($action === 'delete') {
        // Create a response
        $response = new Response(
            "Cookie deleted! You are now a guest again.",
            200,
            ['Content-Type' => 'text/html']
        );
        
        // Add an expired cookie to make the browser delete it
        return $response->withExpiredCookie('name');
    } else {
        // Default action: view
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Cookie Example</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .actions {
            margin-top: 20px;
        }
        .actions a {
            display: inline-block;
            margin-right: 10px;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .actions a.delete {
            background-color: #f44336;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            padding: 8px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Cookie Example</h1>
    
    <div class="card">
        <h2>Current Cookie Value</h2>
        <p>Hello, <strong>{$name}</strong>!</p>
        
        <div class="actions">
            <a href="?action=delete" class="delete">Delete Cookie</a>
        </div>
    </div>
    
    <div class="card">
        <h2>Set Cookie</h2>
        <form method="GET">
            <input type="hidden" name="action" value="set">
            <div class="form-group">
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" value="{$name}" required>
            </div>
            <button type="submit">Save</button>
        </form>
    </div>
</body>
</html>
HTML;

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
};

// Handle the request and get the response
$response = $server->handle($request, $handler);

// Send the response
$server->send($response); 