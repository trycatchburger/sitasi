<?php

namespace App\Exceptions;

/**
 * Exception for database errors
 */
class DatabaseException extends CustomException
{
    public function __construct(string $message = "Database operation failed", int $code = 500, \Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}