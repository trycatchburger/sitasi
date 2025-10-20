<?php

namespace App\Exceptions;

/**
 * Exception for validation errors
 */
class ValidationException extends CustomException
{
    private $errors = [];

    public function __construct(array $errors, string $message = "Validation failed", int $code = 422, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous, ['errors' => $errors]);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}