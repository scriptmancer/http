<?php

declare(strict_types=1);

namespace Nazim\Http\Exception;

use Throwable;

class NotFoundException extends HttpException
{
    public function __construct(
        string $message = 'Not Found',
        array $headers = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 404, $headers, $code, $previous);
    }
} 