<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Nazim\Http\Exception\BadRequestException;
use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;
use Nazim\Http\Server\Server;

// Create a request from globals
$request = Request::fromGlobals();

// Create a server
$server = new Server();

// Define a handler for file uploads
$handler = function (Request $request) {
    // Check if this is a POST request
    if (!$request->isMethod('POST')) {
        // Show the upload form
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>File Upload Example</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        form {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>File Upload Example</h1>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="file">Select file:</label>
            <input type="file" id="file" name="file" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" cols="50"></textarea>
        </div>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
HTML;
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    // Handle file upload
    $file = $request->file('file');

    // Check if a file was uploaded
    if (!$file) {
        throw new BadRequestException('No file uploaded.');
    }

    // Validate file
    if ($file->getError() !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The file exceeds the MAX_FILE_SIZE directive in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
        
        $errorMessage = $errorMessages[$file->getError()] ?? 'Unknown upload error.';
        throw new BadRequestException($errorMessage);
    }

    // Get file info
    $fileName = $file->getClientFilename();
    $fileSize = $file->getSize();
    $fileType = $file->getClientMediaType();
    
    // Get the description
    $description = $request->post('description', '');

    // In a real application, you would save the file
    // $file->moveTo('/path/to/destination/' . $fileName);

    // Return a success response with the file information
    return Response::json([
        'success' => true,
        'message' => 'File uploaded successfully!',
        'file' => [
            'name' => $fileName,
            'size' => $fileSize,
            'type' => $fileType,
        ],
        'description' => $description,
    ]);
};

try {
    // Handle the request and get the response
    $response = $server->handle($request, $handler);
} catch (BadRequestException $e) {
    // Return an error response
    $response = Response::json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 400);
} catch (Exception $e) {
    // Return a server error response
    $response = Response::json([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
    ], 500);
}

// Send the response
$server->send($response); 