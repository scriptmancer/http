<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Scriptmancer\Http\Client\HttpClient;

// Create an HTTP client
$client = new HttpClient([
    'base_uri' => 'https://jsonplaceholder.typicode.com',
    'timeout' => 10,
]);

// Make a GET request
echo "Getting posts...\n";
$response = $client->get('/posts');
$posts = json_decode($response->getBody(), true);
echo "Status code: " . $response->getStatusCode() . "\n";
echo "Number of posts: " . count($posts) . "\n\n";

// Make a POST request
echo "Creating a post...\n";
$response = $client->post('/posts', [
    'json' => [
        'title' => 'foo',
        'body' => 'bar',
        'userId' => 1
    ]
]);
$post = json_decode($response->getBody(), true);
echo "Status code: " . $response->getStatusCode() . "\n";
echo "Created post ID: " . $post['id'] . "\n\n";

// Make a PUT request
echo "Updating a post...\n";
$response = $client->put('/posts/1', [
    'json' => [
        'id' => 1,
        'title' => 'foo updated',
        'body' => 'bar updated',
        'userId' => 1
    ]
]);
$post = json_decode($response->getBody(), true);
echo "Status code: " . $response->getStatusCode() . "\n";
echo "Updated post title: " . $post['title'] . "\n\n";

// Make a DELETE request
echo "Deleting a post...\n";
$response = $client->delete('/posts/1');
echo "Status code: " . $response->getStatusCode() . "\n\n";

// Make a request with error handling
echo "Making a request with error handling...\n";
try {
    $response = $client->get('/nonexistent');
    echo "Status code: " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
} 