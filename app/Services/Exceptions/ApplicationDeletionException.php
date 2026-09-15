<?php

namespace App\Services\Exceptions;

use RuntimeException;

class ApplicationDeletionException extends RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
