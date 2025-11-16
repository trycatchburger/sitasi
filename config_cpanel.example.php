<?php
// Contoh file konfigurasi untuk deployment ke cPanel
// Salin file ini ke config.php dan sesuaikan dengan pengaturan hosting Anda

return [
    'mail' => [
        'host' => 'mail.yourdomain.com',        // Ganti dengan SMTP server domain Anda
        'port' => 587,                          // Port SMTP (587 untuk TLS)
        'username' => 'noreply@yourdomain.com', // Email dari domain Anda
        'password' => 'your-email-password',    // Password email atau app password
        'from_address' => 'noreply@yourdomain.com',
        'from_name' => 'Sistem Pengumpulan Skripsi', // Ganti dengan nama aplikasi Anda
        'admin_email' => 'admin@yourdomain.com'       // Email admin Anda
    ],
    'db' => [
        'host' => 'localhost',                  // Biasanya localhost di cPanel
        'dbname' => 'your_username_sitasi_db',  // Ganti dengan nama database Anda di cPanel
        'username' => 'your_db_username',       // Ganti dengan username database Anda
        'password' => 'your_db_password',       // Ganti dengan password database Anda
        'charset' => 'utf8mb4'
    ],
    'base_path' => '/sitasi', // Ganti dengan nama subdirektori Anda jika berbeda
    'maintenance' => [
        'enabled' => false, // Set ke true jika ingin maintenance mode
        'message' => 'Sistem sedang dalam perawatan. Silakan kembali lagi nanti.',
        'allowed_ips' => [] // IP yang diizinkan mengakses saat maintenance
    ]
];