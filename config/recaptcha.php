<?php
// Configuration for Google reCAPTCHA
// Load environment variables if not already loaded
if (!isset($_ENV['RECAPTCHA_SITE_KEY'])) {
    $dotenvPath = __DIR__ . '/../.env';
    if (file_exists($dotenvPath)) {
        $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
}

return [
    'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? 'your_site_key_here',
    'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? 'your_secret_key_here',
];