<?php

namespace App\Services;

use Throwable;

/**
 * Service for managing user-friendly error messages
 */
class ErrorMessageService
{
    /**
     * Get user-friendly error message based on exception type
     */
    public static function getUserFriendlyMessage(string $exceptionType, string $defaultMessage = 'An error occurred. Please try again.'): string
    {
        $messages = [
            'App\Exceptions\ValidationException' => 'There were issues with the information you provided. Please check your input and try again.',
            'App\Exceptions\FileUploadException' => 'There was a problem uploading your files. Please check the file size and format and try again.',
            'App\Exceptions\DatabaseException' => 'We encountered a database error. Please try again later.',
            'App\Exceptions\AuthenticationException' => 'You are not authorized to perform this action. Please log in and try again.',
            'PDOException' => 'We encountered a database connection error. Please try again later.',
            'Exception' => $defaultMessage,
        ];

        return $messages[$exceptionType] ?? $defaultMessage;
    }

    /**
     * Get technical error message for administrators
     */
    public static function getTechnicalMessage(\Throwable $exception): string
    {
        return sprintf(
            '%s: %s in %s on line %d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    /**
     * Format error details for display
     */
    public static function formatErrorDetails(array $details): string
    {
        if (empty($details)) {
            return '';
        }

        $output = '<ul class="mt-2 text-sm text-left">';
        foreach ($details as $field => $errors) {
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    $output .= '<li>' . htmlspecialchars($error) . '</li>';
                }
            } else {
                $output .= '<li>' . htmlspecialchars($errors) . '</li>';
            }
        }
        $output .= '</ul>';

        return $output;
    }
}