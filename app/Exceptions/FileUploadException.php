<?php

namespace App\Exceptions;

/**
 * Exception for file upload errors
 */
class FileUploadException extends CustomException
{
    public function __construct(string $message = "File upload failed", int $code = 400, \Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}