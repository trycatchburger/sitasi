# Instruksi Deployment Aplikasi ke cPanel sebagai Subdirektori

## Tujuan

Dokumen ini menjelaskan langkah-langkah untuk mengonlinekan aplikasi Sistem Pengumpulan Skripsi (SITASI) ke cPanel sebagai subdirektori dari domain utama.

## Prasyarat

- Akun cPanel hosting dengan dukungan Git
- SSH access (jika tersedia)
- Database MySQL
- Domain atau subdomain untuk deployment
- Kode aplikasi yang siap di repository (GitHub, GitLab, dll)

## Langkah-langkah Deployment

### 1. Menyiapkan Repository di cPanel

1. Login ke akun cPanel Anda
2. Gulir ke bawah ke bagian "Files" 
3. Klik "Git Version Control"
4. Klik "Create" untuk membuat repository baru
5. Isi detail berikut:
   - **Repository URL**: URL repository Anda (misalnya `https://github.com/username/repo.git`)
   - **Repository Path**: Direktori untuk mengkloning repository (misalnya `sitasi-app`)
   - **Branch**: Biasanya `main` atau `master`
6. Klik "Create"

### 2. Menentukan Path Deployment

1. Setelah repository dibuat, klik "Manage" di sebelah repository Anda
2. Di bagian "Deployment Path", tentukan path ke subdirektori:
   - Misalnya: `/home/username/public_html/sitasi`
   - Ganti `username` dengan username cPanel Anda
   - Ganti `sitasi` dengan nama subdirektori yang diinginkan
3. Klik "Deploy" untuk menyalin file dari repository ke path deployment

### 3. Instalasi Dependencies

1. Akses File Manager cPanel atau gunakan SSH jika tersedia
2. Navigasi ke direktori aplikasi Anda
3. Install dependencies PHP dengan Composer:
   - Jika Composer belum terinstal: `curl -sS https://getcomposer.org/installer | php`
   - Jalankan Composer: `php composer.phar install --no-dev`
   - Atau jika Composer tersedia: `composer install --no-dev`

Catatan: Flag `--no-dev` mengabaikan dependencies untuk development yang tidak diperlukan di production.

### 4. Membuat Subdirektori di public_html

Jika Anda ingin menggunakan subdirektori khusus:

1. Di cPanel, pergi ke bagian "Files"
2. Klik "File Manager"
3. Navigasi ke direktori `public_html`
4. Buat direktori baru (misalnya `sitasi`)
5. Ini akan menjadi direktori dasar untuk aplikasi Anda

### 5. Konfigurasi Database

#### Buat Database Baru:
1. Di cPanel, pergi ke bagian "Databases"
2. Klik "MySQL Database Wizard"
3. Buat database baru (misalnya `username_sitasi_db`)
4. Buat user database baru dan berikan password yang kuat
5. Tambahkan user ke database dengan semua hak akses

#### Impor Skema Database:
1. Di cPanel, buka "phpMyAdmin"
2. Pilih database yang baru dibuat
3. Klik tab "Import"
4. Pilih file `database.sql` dari aplikasi
5. Klik "Go" untuk mengimpor skema

### 6. Konfigurasi Aplikasi untuk Subdirektori

#### Update config.php:
Anda perlu memperbarui konfigurasi `base_path` di `config.php` untuk menyesuaikan dengan subdirektori Anda:

```php
<?php
return [
    'mail' => [
        'host' => 'smtp.your-host.com',
        'port' => 587,
        'username' => 'your-email@yourdomain.com',
        'password' => 'your-email-password',
        'from_address' => 'noreply@yourdomain.com',
        'from_name' => 'SITASI App',
        'admin_email' => 'admin@yourdomain.com'
    ],
    'db' => [
        'host' => 'localhost',
        'dbname' => 'your_database_name',
        'username' => 'your_db_username',
        'password' => 'your_db_password',
        'charset' => 'utf8mb4'
    ],
    'base_path' => '/sitasi' // Sesuaikan dengan nama subdirektori Anda
];
?>
```

### 7. Update File .htaccess

#### Update .htaccess di root aplikasi:
1. Di File Manager cPanel, navigasi ke subdirektori Anda
2. Edit file `.htaccess` di root aplikasi (bukan di direktori `public`)
3. Perbarui direktif RewriteBase sesuai subdirektori Anda:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /sitasi/  # Ganti dengan nama subdirektori Anda

    # Redirect ke direktori public
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### Update .htaccess di direktori public:
1. Navigasi ke direktori `public`
2. Edit file `.htaccess`
3. Perbarui direktif RewriteBase:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /sitasi/public/  # Ganti dengan path subdirektori Anda
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php [L]
</IfModule>
```

### 8. Pengaturan Document Root (Opsional)

Jika Anda ingin mengarahkan domain atau subdomain ke aplikasi Anda:

1. Di cPanel, pergi ke "Domains"
2. Klik "Subdomains" atau "Addon Domains" tergantung pengaturan Anda
3. Edit pengaturan domain
4. Set document root ke direktori `public`:
   - Misalnya: `/home/username/public_html/sitasi/public`
   - Jika menggunakan subdomain: `/home/username/public_html/subdomain-name/public`
   - Jika menggunakan subdirektori: `/home/username/public_html/subdirectory-name/public`

### 9. Set Permissions

Pastikan direktori berikut memiliki hak akses tulis:

1. `public/uploads` - Untuk menyimpan file yang diupload
2. `cache` - Untuk caching (jika digunakan)

Untuk mengatur permissions:
1. Di File Manager cPanel, klik kanan pada direktori
2. Pilih "Change Permissions"
3. Atur permissions ke 755 untuk direktori dan 644 untuk file
4. Untuk direktori `public/uploads`, Anda mungkin perlu permissions 775

### 10. Uji Coba Deployment

#### Akses Aplikasi:
1. Kunjungi domain Anda di browser
2. Anda seharusnya melihat halaman utama aplikasi SITASI

#### Uji Fungsi Admin:
1. Navigasi ke halaman login admin (biasanya `/sitasi/admin/login`)
2. Gunakan kredensial default:
   - Username: `admin`
   - Password: `admin123`
3. Ganti password default setelah login pertama

#### Uji Pengiriman Formulir:
1. Navigasi ke formulir pengiriman (biasanya halaman utama)
2. Isi formulir dengan data uji
3. Upload file uji
4. Submit formulir
5. Periksa apakah:
   - Pengiriman dicatat di database
   - Notifikasi email dikirim
   - File diupload dengan benar

## Pembaruan Database dari localhost

### Mencocokkan Skema Database

Jika Anda memiliki perubahan skema database di localhost, Anda perlu menggabungkannya dengan skema produksi. Berikut adalah perubahan umum yang mungkin telah Anda lakukan di localhost:

1. Tabel `users` - untuk manajemen akun pengguna
2. Kolom `submission_type` - untuk mendukung pengiriman jurnal
3. Kolom `abstract` - untuk abstrak jurnal
4. Kolom `serial_number` - untuk pelacakan dokumen
5. Kolom `user_id` - untuk menghubungkan ke akun pengguna
6. Foreign key constraints - untuk integritas data
7. Index kinerja - untuk query yang lebih cepat

### File SQL untuk Update

File-file berikut mungkin perlu digunakan untuk memperbarui database produksi:

- `add_users_table.sql` - membuat tabel users
- `add_journal_submission_support.sql` - menambahkan dukungan jurnal
- `update_user_references_constraint.php` - memperbarui constraint
- `database_indexes.sql` - menambahkan index kinerja

### Langkah-langkah Update Database

1. Backup database produksi sebelum melakukan perubahan
2. Jalankan perintah SQL dari file update secara berurutan
3. Verifikasi bahwa semua tabel dan kolom telah ditambahkan dengan benar
4. Uji fungsionalitas aplikasi setelah update

## Troubleshooting

### 1. "Deploy Head Commit" Tidak Aktif
- Periksa koneksi repository - pastikan repository berhasil dikloning
- Verifikasi pemilihan branch - pastikan branch valid dipilih
- Pastikan repository memiliki setidaknya satu commit

### 2. "500 Internal Server Error"
- Periksa file permissions (seharusnya 644 untuk file, 755 untuk direktori)
- Verifikasi bahwa semua ekstensi PHP yang diperlukan terinstal
- Periksa error logs di cPanel untuk detail lebih lanjut

### 3. "404 Not Found" untuk Halaman
- Pastikan document root diarahkan ke direktori `public`
- Verifikasi bahwa file `.htaccess` ada dan dapat dibaca
- Periksa apakah `mod_rewrite` diaktifkan

### 4. Masalah Koneksi Database
- Verifikasi kredensial database di `config.php`
- Pastikan user database memiliki hak akses yang tepat
- Periksa apakah server database dapat diakses

### 5. Email Tidak Terkirim
- Verifikasi pengaturan SMTP di `config.php`
- Periksa apakah hosting provider Anda mengizinkan koneksi SMTP
- Pastikan kredensial email Anda benar

### 6. Masalah Upload File
- Periksa permissions pada direktori `public/uploads`
- Verifikasi bahwa direktori tersebut ada dan dapat ditulis
- Periksa limit upload PHP di konfigurasi hosting

## Update Otomatis

Untuk memperbarui aplikasi setelah membuat perubahan di repository:

1. Di Git Version Control cPanel, temukan repository Anda
2. Klik "Manage"
3. Klik "Pull or Deploy"
4. Klik "Update from Remote" untuk mengambil perubahan terbaru
5. Klik "Deploy" untuk memperbarui aplikasi live Anda

## Pertimbangan Keamanan

1. **Ganti Kredensial Default**: Segera ganti password admin default
2. **Amankan File Konfigurasi**: Pastikan `config.php` tidak dapat diakses via web
3. **File Permissions**: Gunakan permissions file yang sesuai untuk mencegah akses tidak sah
4. **Update Berkala**: Jaga dependencies Anda tetap update
5. **Backup**: Lakukan backup database dan file secara berkala