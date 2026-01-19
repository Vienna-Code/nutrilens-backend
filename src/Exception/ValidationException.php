<?php

namespace App\Exception;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public function __construct(
        public readonly array $payload,
        public readonly int $status = 400
    ) {
        parent::__construct('Validation failed');
    }
}
