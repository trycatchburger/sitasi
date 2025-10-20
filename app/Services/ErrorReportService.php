<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Service for sending error reports to administrators
 */
class ErrorReportService
{
    private $mailer;
    private $config;

    public function __construct()
    {
        // Load configuration
        $this->config = require __DIR__ . '/../../config.php';
        
        // Initialize PHPMailer
        $this->mailer = new PHPMailer(true);
        $mailConfig = $this->config['mail'];
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = $mailConfig['host'];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $mailConfig['username'];
        $this->mailer->Password   = $mailConfig['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = $mailConfig['port'];
        
        // Sender
        $this->mailer->setFrom($mailConfig['from_address'], $mailConfig['from_name']);
    }

    /**
     * Send error report to administrators
     */
    public function sendErrorReport(string $subject, string $message, array $context = []): bool
    {
        try {
            // Recipient
            $this->mailer->addAddress($this->config['mail']['admin_email']);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $this->formatErrorReport($message, $context);
            $this->mailer->AltBody = strip_tags($this->formatErrorReport($message, $context));
            
            // Send email
            $this->mailer->send();
            
            return true;
        } catch (PHPMailerException $e) {
            // Log the error but don't throw an exception to avoid infinite loops
            error_log("Failed to send error report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format error report for email
     */
    private function formatErrorReport(string $message, array $context): string
    {
        $html = "<h2>Error Report</h2>";
        $html .= "<p><strong>Message:</strong> " . htmlspecialchars($message) . "</p>";
        
        if (!empty($context)) {
            $html .= "<h3>Context:</h3>";
            $html .= "<ul>";
            foreach ($context as $key => $value) {
                if (is_array($value)) {
                    $html .= "<li><strong>{$key}:</strong> " . htmlspecialchars(json_encode($value)) . "</li>";
                } else {
                    $html .= "<li><strong>{$key}:</strong> " . htmlspecialchars((string)$value) . "</li>";
                }
            }
            $html .= "</ul>";
        }
        
        // Add request information
        $html .= "<h3>Request Information:</h3>";
        $html .= "<ul>";
        $html .= "<li><strong>URL:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . "</li>";
        $html .= "<li><strong>Method:</strong> " . htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</li>";
        $html .= "<li><strong>IP:</strong> " . htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</li>";
        $html .= "<li><strong>User Agent:</strong> " . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</li>";
        $html .= "</ul>";
        
        // Add timestamp
        $html .= "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
        
        return $html;
    }

    /**
     * Send critical error report
     */
    public function sendCriticalErrorReport(\Throwable $exception): bool
    {
        $subject = "Critical Error in Application";
        $message = "A critical error occurred in the application";
        
        $context = [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        return $this->sendErrorReport($subject, $message, $context);
    }
}