<?php

namespace App\Services;

/**
 * Comprehensive logging service for the application
 */
class Logger
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    private $logPath;
    private $minLevel;
    private $levels = [
        self::EMERGENCY => 0,
        self::ALERT     => 1,
        self::CRITICAL => 2,
        self::ERROR     => 3,
        self::WARNING   => 4,
        self::NOTICE    => 5,
        self::INFO      => 6,
        self::DEBUG     => 7,
    ];

    public function __construct(string $logPath = null, string $minLevel = self::DEBUG)
    {
        $this->logPath = $logPath ?? __DIR__ . '/../../logs/app.log';
        $this->minLevel = $minLevel;
        
        // Create log directory if it doesn't exist
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * System is unusable.
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Check if the log level should be processed
        if ($this->levels[$level] > $this->levels[$this->minLevel]) {
            return;
        }

        // Format the message
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = $this->formatMessage($level, $timestamp, $message, $context);
        
        // Write to file
        file_put_contents($this->logPath, $formattedMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // Also log to PHP's error log for critical errors
        if (in_array($level, [self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR])) {
            error_log($formattedMessage);
        }
    }

    /**
     * Format the log message
     */
    private function formatMessage(string $level, string $timestamp, string $message, array $context): string
    {
        // Replace placeholders in the message with context values
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $message = str_replace("{{$key}}", (string) $val, $message);
            }
        }

        // Add context as JSON if it's not empty
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' ' . json_encode($context);
        }

        // Get request information if available
        $requestInfo = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestInfo = ' [URI: ' . $_SERVER['REQUEST_URI'] . ']';
        }

        return "[{$timestamp}] [{$level}] {$message}{$contextStr}{$requestInfo}";
    }

    /**
     * Get the log file path
     */
    public function getLogPath(): string
    {
        return $this->logPath;
    }

    /**
     * Clear the log file
     */
    public function clear(): void
    {
        file_put_contents($this->logPath, '');
    }

    /**
     * Get log contents
     */
    public function getLogs(): string
    {
        if (file_exists($this->logPath)) {
            return file_get_contents($this->logPath);
        }
        return '';
    }
}