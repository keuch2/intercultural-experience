<?php

namespace App\Services\ProgramEngine\Exceptions;

use RuntimeException;

class StageTransitionException extends RuntimeException
{
    /** @param string[] $reasons */
    public function __construct(public readonly array $reasons, string $message = 'No se puede avanzar de etapa.')
    {
        parent::__construct($message);
    }
}
