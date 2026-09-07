<?php

namespace App\Services\ProgramEngine\Exceptions;

use RuntimeException;
use Throwable;

class JobPoolException extends RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message, public readonly int $status = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
