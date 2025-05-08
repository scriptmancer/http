<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;
use Nazim\Http\Server\Server;

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Define a handler
$handler = function (Request $request) {
    // Get the response type from the query parameters
    $type = $request->query('type', 'html');
    
    // Create a file path for download/file examples
    $filePath = __DIR__ . '/assets/sample.txt';
    
    // Create the file if it doesn't exist
    if (!file_exists($filePath)) {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($filePath, "This is a sample text file for download and inline viewing.\nLine 2\nLine 3\n");
    }
    
    // Handle different response types
    switch ($type) {
        case 'html':
            return Response::html(buildHtmlPage(), 200);
            
        case 'text':
            return Response::text("This is a plain text response.\nIt can have multiple lines.\nNo HTML formatting is applied.");
            
        case 'xml':
            $xml = '<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="1">
        <name>Item 1</name>
        <value>100</value>
    </item>
    <item id="2">
        <name>Item 2</name>
        <value>200</value>
    </item>
</root>';
            return Response::xml($xml);
            
        case 'json':
            $data = [
                'items' => [
                    ['id' => 1, 'name' => 'Item 1', 'value' => 100],
                    ['id' => 2, 'name' => 'Item 2', 'value' => 200],
                ],
                'count' => 2,
                'status' => 'success'
            ];
            return Response::json($data);
            
        case 'download':
            return Response::download($filePath, 'sample-download.txt');
            
        case 'inline':
            return Response::inline($filePath, 'sample-inline.txt');
            
        case 'file':
            return Response::file($filePath);
            
        case 'stream':
            $counter = 0;
            return Response::stream(function($length) use (&$counter) {
                if ($counter >= 10) {
                    return ''; // End the stream after 10 iterations
                }
                
                $counter++;
                $text = "Stream chunk #{$counter} - " . date('H:i:s') . "\n";
                
                // Add some delay to simulate server processing time
                // In a real app, this might be reading chunks from a large file
                // or processing a large dataset
                usleep(100000); // 100ms delay
                
                return $text;
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'X-Accel-Buffering' => 'no', // Disable buffering in Nginx
            ]);
            
        case 'nocontent':
            return Response::noContent();
            
        case 'created':
            return Response::created('/resource/123', 'Resource created successfully');
            
        case 'error':
            $errorType = $request->query('error', 'bad_request');
            
            switch ($errorType) {
                case 'bad_request':
                    return Response::badRequest('Bad request - invalid parameters');
                case 'unauthorized':
                    return Response::unauthorized('Authentication required');
                case 'forbidden':
                    return Response::forbidden('You do not have access to this resource');
                case 'not_found':
                    return Response::notFound('The requested resource was not found');
                case 'method_not_allowed':
                    return Response::methodNotAllowed(['GET', 'POST'], 'This endpoint only supports GET and POST');
                case 'server_error':
                default:
                    return Response::serverError('An internal server error occurred');
            }
            
        default:
            return Response::html(buildHtmlPage(), 200);
    }
};

/**
 * Build the HTML page with links to all response types
 */
function buildHtmlPage(): string
{
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Response Types Example</title>
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
        .examples {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
        }
        .example {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            background-color: #fff;
            text-align: center;
        }
        .example a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .example a:hover {
            background-color: #f0f0f0;
        }
        h2 {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <h1>Nazim HTTP Response Types Example</h1>
    
    <div class="card">
        <h2>Content Types</h2>
        <div class="examples">
            <div class="example">
                <a href="?type=html">HTML</a>
            </div>
            <div class="example">
                <a href="?type=text">Plain Text</a>
            </div>
            <div class="example">
                <a href="?type=xml">XML</a>
            </div>
            <div class="example">
                <a href="?type=json">JSON</a>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2>File Responses</h2>
        <div class="examples">
            <div class="example">
                <a href="?type=download">Download File</a>
            </div>
            <div class="example">
                <a href="?type=inline">Inline File</a>
            </div>
            <div class="example">
                <a href="?type=file">File Content</a>
            </div>
            <div class="example">
                <a href="?type=stream">Stream Response</a>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2>Status Code Responses</h2>
        <div class="examples">
            <div class="example">
                <a href="?type=nocontent">No Content (204)</a>
            </div>
            <div class="example">
                <a href="?type=created">Created (201)</a>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2>Error Responses</h2>
        <div class="examples">
            <div class="example">
                <a href="?type=error&error=bad_request">Bad Request (400)</a>
            </div>
            <div class="example">
                <a href="?type=error&error=unauthorized">Unauthorized (401)</a>
            </div>
            <div class="example">
                <a href="?type=error&error=forbidden">Forbidden (403)</a>
            </div>
            <div class="example">
                <a href="?type=error&error=not_found">Not Found (404)</a>
            </div>
            <div class="example">
                <a href="?type=error&error=method_not_allowed">Method Not Allowed (405)</a>
            </div>
            <div class="example">
                <a href="?type=error&error=server_error">Server Error (500)</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
}

// Handle the request and get the response
$response = $server->handle($request, $handler);

// Send the response
$server->send($response); 