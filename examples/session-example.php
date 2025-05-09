<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Scriptmancer\Http\Middleware\SessionMiddleware;
use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;
use Scriptmancer\Http\Server\Server;

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Add the session middleware
$sessionMiddleware = new SessionMiddleware([
    'name' => 'NAZIM_SESSION',
    'cookie_lifetime' => 86400 * 30, // 30 days
    'cookie_secure' => false, // Set to true in production with HTTPS
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);
$server->addMiddleware($sessionMiddleware);

// Define a handler
$handler = function (Request $request) {
    // Get the session
    $session = $request->session();
    
    // Get the action from the query parameters
    $action = $request->query('action', 'view');
    
    // Handle different actions
    if ($action === 'increment') {
        // Get the current counter value or start at 0
        $counter = $session->get('counter', 0);
        
        // Increment the counter
        $counter++;
        
        // Save the counter value
        $session->set('counter', $counter);
        
        // Add a flash message
        $session->flash('message', 'Counter incremented!');
        
        // Redirect to the view page
        return Response::redirect('?action=view');
    } elseif ($action === 'decrement') {
        // Get the current counter value or start at 0
        $counter = $session->get('counter', 0);
        
        // Decrement the counter (don't go below 0)
        $counter = max(0, $counter - 1);
        
        // Save the counter value
        $session->set('counter', $counter);
        
        // Add a flash message
        $session->flash('message', 'Counter decremented!');
        
        // Redirect to the view page
        return Response::redirect('?action=view');
    } elseif ($action === 'reset') {
        // Reset the counter
        $session->set('counter', 0);
        
        // Add a flash message
        $session->flash('message', 'Counter reset!');
        
        // Redirect to the view page
        return Response::redirect('?action=view');
    } elseif ($action === 'regenerate') {
        // Regenerate the session ID
        $session->regenerate();
        
        // Add a flash message
        $session->flash('message', 'Session ID regenerated!');
        
        // Redirect to the view page
        return Response::redirect('?action=view');
    } elseif ($action === 'destroy') {
        // Destroy the session
        $session->destroy();
        
        // Create a new response
        $response = new Response(
            "Session destroyed! Redirecting to view page...",
            200,
            ['Content-Type' => 'text/html']
        );
        
        // Add a meta refresh to redirect after 2 seconds
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="2;url=?action=view">
    <title>Session Example</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Session Destroyed!</h1>
    <p>Redirecting to view page...</p>
</body>
</html>
HTML;
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    } else {
        // Default action: view
        
        // Get the current counter value
        $counter = $session->get('counter', 0);
        
        // Get the flash message
        $message = $session->getFlash('message');
        
        // Flash message HTML
        $messageHtml = '';
        if ($message) {
            $messageHtml = "<div class='message'>{$message}</div>";
        }
        
        // Get session data as JSON
        $sessionData = json_encode($session->all(), JSON_PRETTY_PRINT);
        
        // Create HTML
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Session Example</title>
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
        .counter {
            font-size: 2em;
            font-weight: bold;
            margin: 20px 0;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .actions a {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .actions a.decrement {
            background-color: #f44336;
        }
        .actions a.reset {
            background-color: #ff9800;
        }
        .actions a.regenerate {
            background-color: #2196F3;
        }
        .actions a.destroy {
            background-color: #9C27B0;
        }
        .message {
            padding: 10px;
            background-color: #e8f5e9;
            border-left: 5px solid #4CAF50;
            margin-bottom: 20px;
        }
        .session-info {
            font-family: monospace;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
            overflow-x: auto;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Session Example</h1>
    
    <!-- Flash Message -->
    $messageHtml
    
    <div class="card">
        <h2>Counter</h2>
        <div class="counter">{$counter}</div>
        
        <div class="actions">
            <a href="?action=increment">Increment</a>
            <a href="?action=decrement" class="decrement">Decrement</a>
            <a href="?action=reset" class="reset">Reset</a>
        </div>
    </div>
    
    <div class="card">
        <h2>Session Management</h2>
        <div class="actions">
            <a href="?action=regenerate" class="regenerate">Regenerate Session ID</a>
            <a href="?action=destroy" class="destroy">Destroy Session</a>
        </div>
    </div>
    
    <div class="card">
        <h2>Session Information</h2>
        <div class="session-info">
            <p>Session ID: {$session->getId()}</p>
            <p>Session Name: {$session->getName()}</p>
            <p>Session Data: <pre>" . $sessionData . "</pre></p>
        </div>
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