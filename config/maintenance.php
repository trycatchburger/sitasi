<?php
// Konfigurasi maintenance mode

return [
    // Aktifkan maintenance mode (true/false)
    'enabled' => false,
    
    // Deteksi apakah aplikasi berjalan di lingkungan online
    'is_online' => function() {
        // Deteksi berdasarkan domain atau alamat IP
        $server_name = $_SERVER['SERVER_NAME'] ?? '';
        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Jika bukan localhost atau alamat lokal, anggap online
        $local_hosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
        $is_local = in_array($server_name, $local_hosts) || 
                   in_array($remote_addr, $local_hosts) ||
                   strpos($server_name, 'local') !== false;
        
        return !$is_local;
    },
    
    // Pesan maintenance
    'message' => 'Sistem sedang dalam perawatan. Silakan kembali lagi nanti.',
    
    // Waktu maintenance (opsional)
    'start_time' => null, // Format: 'Y-m-d H:i:s'
    'end_time' => null,   // Format: 'Y-m-d H:i:s'
    
    // Mode debug - jika true, maintenance mode akan aktif juga di lingkungan lokal
    'debug_mode' => false,
    
    // CSS untuk notifikasi
    'css' => '
    <style>
        .maintenance-banner {
            background-color: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        .online-banner {
            background-color: #dbeafe;
            border-color: #3b82f6;
            color: #1e3a8a;
        }
        .maintenance-banner, .online-banner {
            font-weight: 500;
        }
        .maintenance-banner i, .online-banner i {
            margin-right: 8px;
        }
    </style>
    '
];