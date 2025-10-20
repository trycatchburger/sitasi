<?php

namespace App\Exceptions;

use Exception;

/**
 * Base custom exception class for the application
 */
class CustomException extends Exception
{
    protected $context = [];

    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}