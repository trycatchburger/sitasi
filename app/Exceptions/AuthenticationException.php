<?php

namespace App\Exceptions;

/**
 * Exception for authentication errors
 */
class AuthenticationException extends CustomException
{
    public function __construct(string $message = "Authentication failed", int $code = 401, \Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}