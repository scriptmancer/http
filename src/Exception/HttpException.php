<?php

declare(strict_types=1);

namespace Nazim\Http\Exception;

use Exception;
use Throwable;

class HttpException extends Exception
{
    protected int $statusCode;
    protected array $headers;

    public function __construct(
        string $message = '',
        int $statusCode = 500,
        array $headers = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }
} 