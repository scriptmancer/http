<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Examples;

use Scriptmancer\Http\Controller\ControllerInterface;
use Scriptmancer\Http\Exception\BadRequestException;
use Scriptmancer\Http\Exception\NotFoundException;
use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

class ApiController implements ControllerInterface
{
    /**
     * Handle the request based on HTTP method
     *
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        $method = $request->method();
        
        return match ($method) {
            'GET' => $this->handleGet($request),
            'POST' => $this->handlePost($request),
            'PUT', 'PATCH' => $this->handlePut($request),
            'DELETE' => $this->handleDelete($request),
            default => throw new BadRequestException("Method {$method} not supported")
        };
    }
    
    /**
     * Handle GET requests
     *
     * @param Request $request
     * @return Response
     */
    private function handleGet(Request $request): Response
    {
        $id = $request->query('id');
        
        if ($id) {
            // Get a single item
            return Response::json([
                'id' => $id,
                'name' => 'Example Item',
                'description' => 'This is an example item'
            ]);
        }
        
        // Get a collection
        return Response::json([
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
        ]);
    }
    
    /**
     * Handle POST requests
     *
     * @param Request $request
     * @return Response
     */
    private function handlePost(Request $request): Response
    {
        $data = $request->postParams();
        
        if (empty($data)) {
            throw new BadRequestException('No data provided');
        }
        
        // Simulate creating a new resource
        return Response::json([
            'id' => 4, // New ID
            'name' => $data['name'] ?? 'New Item',
            'created' => true
        ], 201);
    }
    
    /**
     * Handle PUT requests
     *
     * @param Request $request
     * @return Response
     */
    private function handlePut(Request $request): Response
    {
        $id = $request->query('id');
        
        if (!$id) {
            throw new BadRequestException('No ID provided');
        }
        
        $data = $request->postParams();
        
        if (empty($data)) {
            throw new BadRequestException('No data provided');
        }
        
        // Simulate updating a resource
        if ($id > 3) {
            throw new NotFoundException("Item with ID {$id} not found");
        }
        
        return Response::json([
            'id' => (int) $id,
            'name' => $data['name'] ?? 'Updated Item',
            'updated' => true
        ]);
    }
    
    /**
     * Handle DELETE requests
     *
     * @param Request $request
     * @return Response
     */
    private function handleDelete(Request $request): Response
    {
        $id = $request->query('id');
        
        if (!$id) {
            throw new BadRequestException('No ID provided');
        }
        
        // Simulate deleting a resource
        if ($id > 3) {
            throw new NotFoundException("Item with ID {$id} not found");
        }
        
        return Response::json([
            'id' => (int) $id,
            'deleted' => true
        ]);
    }
} 