# Panduan Maintenance Mode

## Deskripsi
Fitur maintenance mode memungkinkan administrator untuk menampilkan pesan perawatan saat sistem sedang diperbarui atau dalam mode perawatan. Fitur ini juga menampilkan notifikasi saat aplikasi diakses dalam mode online.

## Konfigurasi

### File Konfigurasi
File konfigurasi utama: `config/maintenance.php`

Parameter konfigurasi:
- `enabled`: true/false - mengaktifkan atau menonaktifkan maintenance mode
- `is_online`: fungsi callback - mendeteksi apakah aplikasi berjalan secara online
- `message`: pesan yang ditampilkan saat maintenance mode aktif
- `start_time`: (opsional) waktu mulai maintenance (format: 'Y-m-d H:i:s')
- `end_time`: (opsional) waktu selesai maintenance (format: 'Y-m-d H:i:s')
- `debug_mode`: (opsional) jika true, maintenance mode akan aktif juga di lingkungan lokal
- `css`: (opsional) styling untuk notifikasi

### Aktivasi Maintenance Mode
Untuk mengaktifkan maintenance mode, ubah nilai `enabled` menjadi `true` di file `config/maintenance.php`:

```php
'enabled' => true,
```

### Deteksi Lingkungan Online
Sistem otomatis mendeteksi apakah aplikasi berjalan secara online berdasarkan:
- Server name (SERVER_NAME)
- Alamat IP (REMOTE_ADDR)
- Jika bukan localhost, 127.0.0.1, ::1, atau 0.0.0.0 maka dianggap online

## Fungsi-fungsi Utilitas

File `app/Utils/MaintenanceHelper.php` menyediakan fungsi-fungsi berikut:

- `isMaintenanceEnabled()` - cek apakah maintenance mode aktif
- `isOnline()` - cek apakah aplikasi berjalan online
- `isLocal()` - cek apakah aplikasi berjalan lokal
- `getMaintenanceMessage()` - ambil pesan maintenance
- `showMaintenanceNotification()` - tampilkan notifikasi maintenance
- `showOnlineNotification()` - tampilkan notifikasi online

## Tampilan

### Halaman Maintenance
Saat maintenance mode aktif dan aplikasi diakses secara online, pengguna akan melihat halaman khusus dengan:
- Ikon peringatan
- Judul "Maintenance Mode Aktif"
- Pesan maintenance yang dikonfigurasi
- Informasi kontak administrator

### Notifikasi di Semua Halaman
Saat aplikasi berjalan secara online (baik dalam maintenance mode atau tidak), notifikasi akan muncul di bagian atas halaman:
- Jika dalam maintenance mode: menampilkan pesan maintenance
- Jika dalam mode online normal: menampilkan peringatan bahwa aplikasi sedang online

## Penggunaan

### Aktifkan Maintenance Mode
1. Edit file `config/maintenance.php`
2. Ganti `'enabled' => false` menjadi `'enabled' => true`
3. Opsional: ubah pesan maintenance di parameter `'message'`

### Nonaktifkan Maintenance Mode
1. Edit file `config/maintenance.php`
2. Ganti `'enabled' => true` menjadi `'enabled' => false`

### Aktifkan Maintenance Mode di Lingkungan Lokal
Untuk menguji maintenance mode di lingkungan lokal (localhost), ubah nilai `debug_mode` menjadi `true` di file `config/maintenance.php`:
```php
'debug_mode' => true,
```

### Maintenance dengan Jadwal Waktu
Anda dapat mengatur waktu mulai dan selesai maintenance:

```php
'start_time' => '2025-12-01 02:00:00', // Format: 'Y-m-d H:i:s'
'end_time' => '2025-12-01 06:00:00',   // Format: 'Y-m-d H:i:s'
```

## Catatan Penting
- Maintenance mode hanya aktif saat aplikasi diakses secara online
- Aplikasi yang diakses secara lokal (localhost) tidak akan melihat maintenance mode
- Fungsi deteksi online/offline dapat dimodifikasi sesuai kebutuhan di file `config/maintenance.php`

## Pengujian
File `test_maintenance.php` disediakan untuk membantu pengujian fungsi maintenance mode secara lokal. Jalankan file ini untuk:
- Memeriksa status online/local
- Memeriksa status aktif/nonaktif maintenance mode
- Melihat contoh tampilan notifikasi
- Melakukan simulasi pengujian

Untuk menguji secara menyeluruh:
1. Jalankan `test_maintenance.php` untuk verifikasi fungsi
2. Aktifkan maintenance mode di `config/maintenance.php`
3. Akses aplikasi melalui domain online
4. Verifikasi bahwa halaman maintenance tampil dengan benar
5. Nonaktifkan maintenance mode untuk kembali ke mode normal