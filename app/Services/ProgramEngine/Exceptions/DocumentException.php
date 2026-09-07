<?php

namespace App\Services\ProgramEngine\Exceptions;

use RuntimeException;

class DocumentException extends RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message, public readonly int $status = 422)
    {
        parent::__construct($message);
    }
}
