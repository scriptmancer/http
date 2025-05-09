<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Exception;

use Throwable;

class UnauthorizedException extends HttpException
{
    public function __construct(
        string $message = 'Unauthorized',
        array $headers = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 401, $headers, $code, $previous);
    }
} 