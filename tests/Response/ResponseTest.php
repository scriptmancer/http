<?php

declare(strict_types=1);

namespace Nazim\Http\Tests\Response;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Nazim\Http\Response\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $response = new Response('Hello, World!', 200, ['Content-Type' => 'text/plain']);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello, World!', $response->getBody());
        $this->assertEquals(['Content-Type' => ['text/plain']], $response->getHeaders());
    }
    
    public function testFromPsrResponse(): void
    {
        $psrResponse = new PsrResponse(
            404,
            ['Content-Type' => 'text/html'],
            'Not Found'
        );
        
        $response = Response::fromPsrResponse($psrResponse);
        
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getBody());
        $this->assertEquals(['Content-Type' => ['text/html']], $response->getHeaders());
    }
    
    public function testWithStatus(): void
    {
        $response = new Response('Hello, World!', 200);
        $newResponse = $response->withStatus(404, 'Not Found');
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(404, $newResponse->getStatusCode());
    }
    
    public function testJsonResponse(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $response = Response::json($data, 201);
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('{"name":"John","age":30}', $response->getBody());
        $this->assertEquals('application/json', $response->getHeader('Content-Type')[0] ?? '');
    }
    
    public function testRedirectResponse(): void
    {
        $response = Response::redirect('https://example.com', 302);
        
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('', $response->getBody());
        $this->assertEquals('https://example.com', $response->getHeader('Location')[0] ?? '');
    }
} 