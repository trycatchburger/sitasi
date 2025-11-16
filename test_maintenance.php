<?php
// File uji untuk maintenance mode
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Utils/MaintenanceHelper.php';

use App\Utils\MaintenanceHelper;

echo "<h2>Uji Maintenance Mode</h2>";

echo "<p>Status Online: " . (MaintenanceHelper::isOnline() ? "Online" : "Offline") . "</p>";
echo "<p>Status Local: " . (MaintenanceHelper::isLocal() ? "Local" : "Remote") . "</p>";
echo "<p>Maintenance Mode Aktif: " . (MaintenanceHelper::isMaintenanceEnabled() ? "Ya" : "Tidak") . "</p>";
echo "<p>Pesan Maintenance: " . MaintenanceHelper::getMaintenanceMessage() . "</p>";

echo "<h3>Contoh Tampilan Notifikasi:</h3>";
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
MaintenanceHelper::showMaintenanceNotification();
MaintenanceHelper::showOnlineNotification();
echo "</div>";

echo "<h3>Langkah-langkah Pengujian:</h3>";
echo "<ol>";
echo "<li>Ubah konfigurasi di config/maintenance.php untuk mengaktifkan maintenance mode</li>";
echo "<li>Akses aplikasi melalui domain online (bukan localhost)</li>";
echo "<li>Periksa apakah notifikasi muncul dengan benar</li>";
echo "<li>Untuk menguji mode online di lokal, ubah fungsi is_online() sementara</li>";
echo "</ol>";