<?php

declare(strict_types=1);

namespace Nazim\Http\Controller;

use Nazim\Http\Request\Request;
use Nazim\Http\Response\Response;

interface ControllerInterface
{
    /**
     * Handle the request and return a response
     *
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response;
} 