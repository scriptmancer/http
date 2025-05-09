<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Tests\Request;

use GuzzleHttp\Psr7\ServerRequest;
use Scriptmancer\Http\Request\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testCreateFromServerRequest(): void
    {
        $serverRequest = new ServerRequest('GET', 'https://example.com');
        $request = new Request($serverRequest);
        
        $this->assertEquals('GET', $request->method());
        $this->assertEquals('https://example.com', (string) $request->uri());
    }
    
    public function testCreate(): void
    {
        $request = Request::create('POST', 'https://example.com/api', ['Content-Type' => 'application/json']);
        
        $this->assertEquals('POST', $request->method());
        $this->assertEquals('https://example.com/api', (string) $request->uri());
        $this->assertEquals('application/json', $request->header('Content-Type'));
    }
    
    public function testIsMethod(): void
    {
        $request = Request::create('POST', 'https://example.com');
        
        $this->assertTrue($request->isMethod('POST'));
        $this->assertTrue($request->isMethod('post'));
        $this->assertFalse($request->isMethod('GET'));
    }
    
    public function testQueryParams(): void
    {
        $serverRequest = new ServerRequest(
            'GET',
            'https://example.com',
            [],
            null,
            '1.1',
            [],
            [],
            ['name' => 'John', 'age' => '30']
        );
        $request = new Request($serverRequest);
        
        $this->assertEquals('John', $request->query('name'));
        $this->assertEquals('30', $request->query('age'));
        $this->assertNull($request->query('unknown'));
        $this->assertEquals('default', $request->query('unknown', 'default'));
    }
} 