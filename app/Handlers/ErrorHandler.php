<?php

namespace App\Handlers;

use App\Exceptions\CustomException;
use App\Exceptions\ValidationException;
use App\Exceptions\FileUploadException;
use App\Exceptions\DatabaseException;
use App\Exceptions\AuthenticationException;
use App\Services\ErrorMessageService;
use App\Services\Logger;
use App\Services\ErrorReportService;
use Exception;
use ErrorException;
use Throwable;

/**
 * Centralized error handler for the application
 */
class ErrorHandler
{
    private static $logger;
    private static $errorReporter;

    /**
     * Register the error handler
     */
    public static function register(): void
    {
        // Initialize logger
        self::$logger = new Logger();
        
        // Initialize error reporter
        self::$errorReporter = new ErrorReportService();
        
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $exception): void
    {
        // Log the exception
        self::logException($exception);

        // Send critical error report for serious exceptions
        if (!($exception instanceof CustomException) ||
            $exception instanceof DatabaseException) {
            self::sendErrorReport($exception);
        }

        // Determine response based on exception type
        if ($exception instanceof ValidationException) {
            self::handleValidationException($exception);
        } elseif ($exception instanceof FileUploadException) {
            self::handleFileUploadException($exception);
        } elseif ($exception instanceof DatabaseException) {
            self::handleDatabaseException($exception);
        } elseif ($exception instanceof AuthenticationException) {
            self::handleAuthenticationException($exception);
        } else {
            self::handleGenericException($exception);
        }
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Handle shutdown errors
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $exception = new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );
            
            self::logException($exception);
            self::sendErrorReport($exception);
            self::renderErrorPage(500, ErrorMessageService::getUserFriendlyMessage('Exception', 'A fatal error occurred.'));
        }
    }

    /**
     * Handle validation exceptions
     */
    private static function handleValidationException(ValidationException $exception): void
    {
        http_response_code(422);
        self::renderErrorPage(422, ErrorMessageService::getUserFriendlyMessage(get_class($exception)), $exception->getErrors());
    }

    /**
     * Handle file upload exceptions
     */
    private static function handleFileUploadException(FileUploadException $exception): void
    {
        http_response_code(400);
        self::renderErrorPage(400, ErrorMessageService::getUserFriendlyMessage(get_class($exception), $exception->getMessage()));
    }

    /**
     * Handle database exceptions
     */
    private static function handleDatabaseException(DatabaseException $exception): void
    {
        http_response_code(500);
        self::renderErrorPage(500, ErrorMessageService::getUserFriendlyMessage(get_class($exception)));
    }

    /**
     * Handle authentication exceptions
     */
    private static function handleAuthenticationException(AuthenticationException $exception): void
    {
        http_response_code(401);
        self::renderErrorPage(401, ErrorMessageService::getUserFriendlyMessage(get_class($exception), $exception->getMessage()));
    }

    /**
     * Handle generic exceptions
     */
    private static function handleGenericException(Throwable $exception): void
    {
        http_response_code(500);
        self::renderErrorPage(500, ErrorMessageService::getUserFriendlyMessage(get_class($exception)));
    }

    /**
     * Log exception details
     */
    private static function logException(Throwable $exception): void
    {
        // Log with our logger service
        if (self::$logger) {
            self::$logger->error(
                ErrorMessageService::getTechnicalMessage($exception),
                [
                    'trace' => $exception->getTraceAsString(),
                    'context' => $exception instanceof CustomException ? $exception->getContext() : []
                ]
            );
        }
        
        // Also log to PHP's error log for critical errors
        if ($exception instanceof DatabaseException ||
            $exception instanceof ErrorException ||
            !($exception instanceof CustomException)) {
            error_log(ErrorMessageService::getTechnicalMessage($exception));
        }
    }

    /**
     * Send error report to administrators
     */
    private static function sendErrorReport(Throwable $exception): void
    {
        if (self::$errorReporter) {
            self::$errorReporter->sendCriticalErrorReport($exception);
        }
    }

    /**
     * Render error page
     */
    private static function renderErrorPage(int $code, string $message, array $details = []): void
    {
        // For AJAX requests, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]);
            return;
        }

        // For regular requests, show error page
        http_response_code($code);
        
        // Pass message and details to the view
        $GLOBALS['errorMessage'] = $message;
        $GLOBALS['errorDetails'] = ErrorMessageService::formatErrorDetails($details);
        
        // Include error view
        $viewPath = __DIR__ . '/../views/errors/' . $code . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Fallback to generic error page
            require_once __DIR__ . '/../views/errors/generic.php';
        }
    }
}