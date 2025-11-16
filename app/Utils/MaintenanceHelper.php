<?php

namespace App\Utils;

class MaintenanceHelper
{
    private static $config = null;
    
    public static function getConfig()
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/maintenance.php';
        }
        return self::$config;
    }
    
    /**
     * Cek apakah maintenance mode diaktifkan
     */
    public static function isMaintenanceEnabled()
    {
        $config = self::getConfig();
        
        // Cek apakah maintenance diaktifkan
        if (!$config['enabled']) {
            return false;
        }
        
        // Cek apakah saat ini dalam rentang waktu maintenance (jika ditentukan)
        if ($config['start_time'] && $config['end_time']) {
            $now = date('Y-m-d H:i:s');
            if ($now < $config['start_time'] || $now > $config['end_time']) {
                return false;
            }
        }
        
        // Jika debug_mode aktif, abaikan pengecekan online
        if (isset($config['debug_mode']) && $config['debug_mode']) {
            return true;
        }
        
        // Cek apakah aplikasi berjalan di lingkungan online
        return self::isOnline();
    }
    
    /**
     * Cek apakah aplikasi berjalan di lingkungan online
     */
    public static function isOnline()
    {
        $config = self::getConfig();
        return $config['is_online']();
    }
    
    /**
     * Cek apakah aplikasi berjalan di lingkungan local
     */
    public static function isLocal()
    {
        return !self::isOnline();
    }
    
    /**
     * Dapatkan pesan maintenance
     */
    public static function getMaintenanceMessage()
    {
        $config = self::getConfig();
        return $config['message'];
    }
    
    /**
     * Tampilkan notifikasi maintenance jika diperlukan
     */
    public static function showMaintenanceNotification()
    {
        $config = self::getConfig();
        if (isset($config['css'])) {
            echo $config['css'];
        }
        
        if (self::isMaintenanceEnabled()) {
            $message = self::getMaintenanceMessage();
            $isOnline = self::isOnline();
            
            if ($isOnline) {
                echo '<div class="maintenance-banner bg-yellow-100 border-b border-yellow-400 text-yellow-800 px-4 py-3 text-center text-sm">';
                echo '<i class="fas fa-exclamation-triangle mr-2"></i>';
                echo '<strong>Maintenance Mode:</strong> ' . htmlspecialchars($message);
                echo '</div>';
            }
        }
    }
    
    /**
     * Tampilkan notifikasi online jika bukan dalam maintenance mode
     */
    public static function showOnlineNotification()
    {
        $config = self::getConfig();
        if (!isset($config['css']) && self::isOnline()) {
            // Tampilkan CSS default jika belum ditampilkan
            echo '
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
            </style>';
        }
        
        if (!self::isMaintenanceEnabled() && self::isOnline()) {
            echo '<div class="online-banner bg-blue-100 border-b border-blue-400 text-blue-800 px-4 py-3 text-center text-sm">';
            echo '<i class="fas fa-globe mr-2"></i>';
            echo '<strong>PERHATIAN:</strong> Anda sedang mengakses aplikasi dalam mode ONLINE';
            echo '</div>';
        }
    }
}