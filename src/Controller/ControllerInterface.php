<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Controller;

use Scriptmancer\Http\Request\Request;
use Scriptmancer\Http\Response\Response;

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