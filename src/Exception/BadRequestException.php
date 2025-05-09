<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Exception;

use Throwable;

class BadRequestException extends HttpException
{
    public function __construct(
        string $message = 'Bad Request',
        array $headers = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 400, $headers, $code, $previous);
    }
} 