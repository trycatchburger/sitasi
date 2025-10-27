# Dokumentasi Aplikasi Sistem Pengunggahan Skripsi dan Jurnal

## Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Struktur Aplikasi](#struktur-aplikasi)
3. [Routing dan Metode](#routing-dan-metode)
4. [Struktur Database](#struktur-database)
5. [Fitur Keamanan](#fitur-keamanan)
6. [Proses Pengunggahan File](#proses-pengunggahan-file)
7. [Fungsi Admin](#fungsi-admin)
8. [Cara Menjalankan Aplikasi](#cara-menjalankan-aplikasi)

## Pendahuluan

Aplikasi ini adalah sistem pengunggahan skripsi dan jurnal yang dibangun dengan PHP murni tanpa framework. Aplikasi ini memungkinkan mahasiswa untuk mengunggah skripsi dan jurnal mereka secara mandiri, serta administrator untuk menyetujui atau menolak pengunggahan tersebut.

### Fitur Utama:
- Pengunggahan skripsi sarjana dan tesis pasca sarjana
- Pengunggahan jurnal ilmiah
- Sistem validasi file
- Sistem otentikasi admin
- Manajemen status pengunggahan
- Notifikasi email otomatis
- Pencarian dan filterisasi konten

## Struktur Aplikasi

Aplikasi ini mengikuti pola arsitektur MVC (Model-View-Controller) dengan struktur direktori sebagai berikut:

```
sitasi/
├── app/
│   ├── Controllers/      # File-file controller untuk menangani logika aplikasi
│   ├── Exceptions/       # File-file kustom exception
│   ├── Handlers/         # File-file handler seperti error handler
│   ├── helpers/          # Fungsi-fungsi pembantu
│   ├── Middleware/       # File-file middleware untuk otentikasi dan validasi
│   ├── Models/           # File-file model untuk interaksi database
│   ├── Repositories/     # File-file repository untuk operasi database
│   ├── Services/         # File-file layanan tambahan
│   └── views/           # File-file tampilan (frontend)
├── public/              # File-file publik seperti CSS, JS, dan file upload
├── config/              # File-file konfigurasi
├── src/                 # File-file sumber seperti CSS dan gambar
├── vendor/              # Folder dari Composer (dependency manager)
├── database.sql         # File struktur database awal
├── config.php           # File konfigurasi utama (email, path, dll)
└── public/index.php     # File utama yang menangani routing
```

### Penjelasan Masing-Masing Folder:

#### 1. `app/Controllers/`
Berisi file-file controller yang menangani logika permintaan HTTP. Setiap controller memiliki metode-metode yang menangani berbagai fungsi aplikasi.

- **SubmissionController.php**: Menangani pengunggahan skripsi, tesis, dan jurnal
- **AdminController.php**: Menangani fungsi-fungsi admin seperti login, dashboard, dan manajemen status

#### 2. `app/Models/`
Berisi file-file model yang menangani interaksi dengan database dan logika bisnis.

- **Submission.php**: Model utama untuk pengunggahan skripsi dan jurnal
- **Admin.php**: Model untuk manajemen akun admin
- **ValidationService.php**: Model untuk validasi data dan file
- **EmailService.php**: Model untuk mengirim email notifikasi

#### 3. `app/Views/`
Berisi file-file tampilan (HTML) yang ditampilkan ke pengguna.

- **home.php**: Halaman utama aplikasi
- **unggah_skripsi.php**: Formulir pengunggahan skripsi
- **unggah_tesis.php**: Formulir pengunggahan tesis
- **unggah_jurnal.php**: Formulir pengunggahan jurnal
- **repository.php**: Halaman daftar skripsi yang telah disetujui
- **journal_repository.php**: Halaman daftar jurnal yang telah disetujui
- **login.php**: Formulir login admin
- **dashboard.php**: Dashboard admin

#### 4. `app/Middleware/`
Berisi file-file middleware yang menangani otentikasi dan validasi permintaan.

- **AuthMiddleware.php**: Middleware untuk memeriksa apakah pengguna sudah login sebagai admin
- **CsrfMiddleware.php**: Middleware untuk mencegah serangan CSRF

#### 5. `app/Services/`
Berisi layanan tambahan yang digunakan aplikasi.

- **CacheService.php**: Layanan untuk caching data
- **Logger.php**: Layanan untuk mencatat log aktivitas

#### 6. `app/Repositories/`
Berisi file-file repository yang menangani operasi database spesifik.

- **SubmissionRepository.php**: Repository untuk operasi database pengunggahan
- **AdminRepository.php**: Repository untuk operasi database admin

#### 7. `public/`
Berisi file-file publik yang dapat diakses langsung melalui web.

- **uploads/**: Folder untuk menyimpan file-file yang diunggah oleh pengguna
- **css/style.css**: File CSS untuk tampilan
- **images/**: Folder untuk menyimpan gambar

## Routing dan Metode

Aplikasi ini menggunakan sistem routing sederhana yang ditangani di `public/index.php`. Routing bekerja dengan format `/controller/method/parameter`.

### Routing Utama:

#### SubmissionController (Pengunggahan):
- `/` → Menampilkan halaman utama (home.php)
- `/submission/skripsi` → Menampilkan formulir pengunggahan skripsi
- `/submission/tesis` → Menampilkan formulir pengunggahan tesis
- `/submission/jurnal` → Menampilkan formulir pengunggahan jurnal
- `/submission/create` → Menyimpan pengunggahan skripsi baru
- `/submission/createMaster` → Menyimpan pengunggahan tesis baru
- `/submission/createJournal` → Menyimpan pengunggahan jurnal baru
- `/submission/resubmit` → Menyimpan perubahan pada pengunggahan yang sudah ada
- `/submission/repository` → Menampilkan daftar skripsi yang disetujui
- `/submission/journalRepository` → Menampilkan daftar jurnal yang disetujui
- `/submission/detail/{id}` → Menampilkan detail skripsi/jurnal
- `/submission/journalDetail/{id}` → Menampilkan detail jurnal

#### AdminController (Admin):
- `/admin/login` → Halaman login admin
- `/admin/dashboard` → Dashboard admin untuk mengelola pengunggahan
- `/admin/logout` → Logout admin
- `/admin/updateStatus` → Memperbarui status pengunggahan (terima/tolak)
- `/admin/repositoryManagement` → Manajemen repository (publikasi/tidak)
- `/admin/create` → Membuat akun admin baru
- `/admin/unpublishFromRepository` → Membatalkan publikasi dari repository
- `/admin/republishToRepository` → Memublikasikan kembali ke repository
- `/admin/adminManagement` → Manajemen akun admin
- `/admin/deleteAdmin` → Menghapus akun admin

### Penjelasan Metode Penting:

#### SubmissionController:

**`skripsi()`** - Menampilkan formulir pengunggahan skripsi sarjana
- File tampilan: `unggah_skripsi.php`
- Input yang diperlukan: nama mahasiswa, NIM, email, dosen pembimbing, judul skripsi, program studi, tahun publikasi, dan file-file pendukung

**`tesis()`** - Menampilkan formulir pengunggahan tesis pasca sarjana
- File tampilan: `unggah_tesis.php`
- Input yang diperlukan: sama dengan skripsi tetapi dengan tipe "master"

**`jurnal()`** - Menampilkan formulir pengunggahan jurnal ilmiah
- File tampilan: `unggah_jurnal.php`
- Input yang diperlukan: nama penulis, email, judul jurnal, abstrak, tahun publikasi, dan file pendukung

**`create()`** - Menyimpan pengunggahan skripsi baru atau memperbarui yang sudah ada
- Melakukan validasi terhadap data formulir dan file
- Normalisasi input (huruf kapital di awal kata)
- Mengecek apakah NIM sudah ada (jika ada, ini adalah pengunggahan ulang)
- Menyimpan ke database dan mengunggah file

**`createMaster()`** - Menyimpan pengunggahan tesis baru
- Sama dengan `create()` tetapi untuk tipe "master"

**`createJournal()`** - Menyimpan pengunggahan jurnal baru
- Sama dengan `create()` tetapi untuk tipe "journal"
- Menggunakan nama penulis sebagai pengenal unik

**`repository()`** - Menampilkan daftar skripsi yang disetujui
- Menyediakan fitur pencarian dan filterisasi
- Mendukung pagination (10 item per halaman)
- Filter berdasarkan tahun dan program studi

**`journalRepository()`** - Menampilkan daftar jurnal yang disetujui
- Sama dengan `repository()` tetapi hanya untuk jurnal

#### AdminController:

**`login()`** - Menangani proses login admin
- Memvalidasi username dan password
- Menggunakan password hashing untuk keamanan
- Mengatur session setelah login berhasil

**`dashboard()`** - Menampilkan dashboard admin
- Menampilkan daftar pengunggahan yang menunggu persetujuan
- Mendukung pencarian dan filterisasi
- Menyediakan pagination

**`updateStatus()`** - Memperbarui status pengunggahan
- Mengubah status menjadi "Diterima", "Ditolak", atau "Pending"
- Mengirim email notifikasi otomatis ke pengguna
- Memerlukan CSRF token untuk keamanan

## Struktur Database

Aplikasi ini menggunakan database MySQL dengan dua tabel utama:

### 1. Tabel `submissions`
Menyimpan informasi dasar tentang pengunggahan skripsi dan jurnal.

**Kolom-kolom:**
- `id` (int, PRIMARY KEY, AUTO_INCREMENT): ID unik untuk setiap pengunggahan
- `serial_number` (varchar(100), NULL): Nomor seri untuk identifikasi unik (opsional)
- `admin_id` (int, NULL): ID admin yang menyetujui (foreign key ke tabel admins)
- `nama_mahasiswa` (varchar(255)): Nama mahasiswa/pengirim
- `nim` (varchar(50)): NIM mahasiswa (UNIQUE)
- `email` (varchar(255)): Email kontak
- `dosen1` (varchar(255)): Nama dosen pembimbing pertama
- `dosen2` (varchar(255)): Nama dosen pembimbing kedua
- `judul_skripsi` (text): Judul skripsi/jurnal
- `program_studi` (varchar(100)): Program studi (hanya untuk skripsi/tesis)
- `abstract` (text, NULL): Abstrak (hanya untuk jurnal)
- `tahun_publikasi` (year): Tahun publikasi
- `submission_type` (enum): Jenis pengunggahan ('bachelor', 'master', 'journal')
- `status` (enum): Status ('Pending', 'Diterima', 'Ditolak', 'Digantikan')
- `keterangan` (text, NULL): Keterangan/revisi dari admin
- `notifikasi` (varchar(255), NULL): Notifikasi tambahan
- `created_at` (timestamp): Waktu pembuatan
- `updated_at` (timestamp): Waktu terakhir diperbarui

### 2. Tabel `submission_files`
Menyimpan informasi tentang file-file yang diunggah.

**Kolom-kolom:**
- `id` (int, PRIMARY KEY, AUTO_INCREMENT): ID unik untuk setiap file
- `submission_id` (int): ID pengunggahan terkait (foreign key ke tabel submissions)
- `file_path` (text): Path lengkap file di server
- `file_name` (varchar(255)): Nama file asli
- `uploaded_at` (timestamp): Waktu file diunggah

### 3. Tabel `admins`
Menyimpan informasi login admin.

**Kolom-kolom:**
- `id` (int, PRIMARY KEY, AUTO_INCREMENT): ID unik admin
- `username` (varchar(50), UNIQUE): Username login
- `password_hash` (varchar(255)): Password dalam bentuk hash
- `created_at` (timestamp): Waktu pembuatan akun

## Fitur Keamanan

Aplikasi ini dilengkapi dengan berbagai fitur keamanan untuk melindungi dari ancaman umum:

### 1. Validasi File
- **Validasi ekstensi file**: Hanya file dengan ekstensi tertentu yang diperbolehkan (PDF, DOC, DOCX)
- **Validasi ukuran file**: Membatasi ukuran file maksimum yang diunggah
- **Validasi konten file**: Memeriksa apakah konten file sesuai dengan ekstensinya
- **Sanitasi nama file**: Membersihkan nama file dari karakter berbahaya

### 2. Validasi Input
- **Validasi formulir**: Memeriksa data formulir sebelum disimpan
- **Sanitasi input**: Membersihkan data input dari karakter berbahaya
- **Normalisasi format**: Mengubah format nama dan judul menjadi format standar

### 3. Proteksi CSRF
- Menggunakan token CSRF untuk mencegah serangan Cross-Site Request Forgery
- Token ini dibutuhkan untuk operasi penting seperti update status

### 4. Otentikasi Admin
- Password di-hash menggunakan algoritma bcrypt
- Session management untuk menjaga status login
- Middleware otentikasi untuk melindungi area admin

### 5. SQL Injection Prevention
- Menggunakan prepared statements untuk semua query database
- Mencegah injeksi SQL dengan parameter binding

### 6. XSS Prevention
- Menggunakan `htmlspecialchars()` untuk membersihkan output ke browser
- Mencegah serangan cross-site scripting

## Proses Pengunggahan File

### Langkah-langkah Pengunggahan:

1. **Pengguna mengakses formulir** - Mahasiswa mengakses formulir pengunggahan sesuai jenisnya (skripsi, tesis, atau jurnal)

2. **Pengisian formulir** - Mahasiswa mengisi informasi seperti nama, NIM, judul, dll.

3. **Pemilihan file** - Mahasiswa memilih file-file yang akan diunggah sesuai kebutuhan

4. **Validasi awal** - Sistem memvalidasi semua input dan file sebelum disimpan

5. **Proses upload** - File diunggah ke server dan nama file diubah menjadi format unik

6. **Simpan ke database** - Informasi pengunggahan dan path file disimpan ke database

7. **Status awal** - Pengunggahan memiliki status "Pending" dan menunggu persetujuan admin

### Proses Validasi File:

1. **Validasi ekstensi** - Memastikan file memiliki ekstensi yang diperbolehkan
2. **Validasi ukuran** - Memastikan file tidak melebihi batas maksimum
3. **Validasi konten** - Memeriksa apakah isi file sesuai dengan ekstensinya
4. **Sanitasi nama** - Membersihkan nama file dari karakter berbahaya
5. **Pembuatan nama unik** - Memberi nama file baru yang unik untuk mencegah konflik

## Fungsi Admin

### Login Admin:
- Akses: `/admin/login`
- Admin harus login untuk mengakses dashboard dan fungsi admin
- Username dan password disimpan dalam database dengan hashing

### Dashboard Admin:
- Akses: `/admin/dashboard`
- Menampilkan daftar pengunggahan yang menunggu persetujuan
- Mendukung pencarian dan filterisasi
- Menyediakan tombol untuk menyetujui atau menolak pengunggahan

### Fungsi-Fungsi Admin:

#### 1. Persetujuan Pengunggahan
- Admin dapat menyetujui atau menolak pengunggahan
- Jika disetujui, status berubah menjadi "Diterima" dan muncul di repository
- Jika ditolak, status berubah menjadi "Ditolak" dan tidak muncul di repository
- Admin dapat menambahkan keterangan/revisi saat menolak

#### 2. Manajemen Repository
- `/admin/repositoryManagement` - Halaman untuk mengelola publikasi
- Admin dapat mempublikasikan kembali atau membatalkan publikasi
- Membatalkan publikasi mengubah status menjadi "Pending" tanpa memberi tahu pengguna

#### 3. Manajemen Admin
- `/admin/adminManagement` - Halaman untuk mengelola akun admin
- Admin dapat membuat akun admin baru atau menghapus akun yang tidak diperlukan

#### 4. Notifikasi Email
- Sistem otomatis mengirim email notifikasi ke pengguna saat status diubah
- Email berisi informasi tentang status terbaru dan keterangan jika ada

## Cara Menjalankan Aplikasi

### Prasyarat:
- PHP 8.0 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache, Nginx, atau PHP built-in server)

### Instalasi:

1. **Clone atau download aplikasi** ke direktori web server Anda

2. **Install dependency** dengan Composer:
   ```bash
   composer install
   ```

3. **Konfigurasi database**:
   - Buat database baru di MySQL
   - Import file `database.sql` ke database Anda
   - Jalankan juga file SQL tambahan untuk fitur-fitur baru:
     - `add_journal_submission_support.sql`
     - `add_serial_number_column.sql`

4. **Konfigurasi aplikasi**:
   - Edit file `config.php` untuk mengatur koneksi database dan SMTP email
   - Atur `base_path` jika aplikasi tidak berada di root domain

5. **Konfigurasi SMTP** (untuk notifikasi email):
   - Edit bagian `mail` di file `config.php`
   - Gunakan SMTP yang valid (seperti Gmail, SendGrid, dll)
   - Gunakan App Password untuk Gmail

6. **Jalankan aplikasi**:
   - Gunakan web server Anda atau PHP built-in server:
     ```bash
     php -S localhost:8000 -t public/
     ```

### Konfigurasi Database:
Ubah file `config/database.php` (atau konfigurasi di `config.php`) dengan informasi database Anda:
```php
'database' => [
    'host' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'your_database_name',
    'charset' => 'utf8mb4'
]
```

### Konfigurasi Email:
```php
'mail' => [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your_email@gmail.com',
    'password' => 'your_app_password',
    'from_address' => 'your_email@gmail.com',
    'from_name' => 'Nama Aplikasi',
    'admin_email' => 'admin_email@gmail.com'
]
```

## Penyesuaian untuk Pengembangan Lebih Lanjut

### Menambahkan Tipe Pengunggahan Baru:
1. Tambahkan nilai baru ke enum `submission_type` di database
2. Buat metode baru di `SubmissionController`
3. Buat formulir tampilan baru
4. Tambahkan validasi di `ValidationService`

### Menambahkan Fitur Baru:
1. Buat controller baru jika diperlukan
2. Tambahkan routing di `public/index.php` jika tidak sesuai pola otomatis
3. Buat model dan repository jika memerlukan operasi database baru
4. Buat view baru untuk tampilan

### Modifikasi Tampilan:
- File CSS utama: `public/css/style.css`
- File HTML utama: `app/views/main.php` (kerangka utama)
- Komponen-komponen: `app/views/header.php`, `app/views/footer.php`, dll.

## Troubleshooting Umum

### 1. Error "Call to undefined function"
- Pastikan Composer sudah dijalankan: `composer install`
- Pastikan autoloader sudah di-include di file utama

### 2. Error koneksi database
- Periksa konfigurasi database di file konfigurasi
- Pastikan MySQL server berjalan
- Pastikan database sudah dibuat dan struktur tabel sudah diimport

### 3. Error upload file
- Periksa izin folder `public/uploads/` (harus bisa ditulis)
- Periksa batas ukuran file di konfigurasi PHP (`upload_max_filesize`, `post_max_size`)

### 4. Error SMTP
- Pastikan SMTP diaktifkan di akun email
- Gunakan App Password bukan password utama untuk Gmail
- Periksa firewall dan pengaturan jaringan

## Penutup

Aplikasi ini dirancang untuk memudahkan proses pengunggahan skripsi dan jurnal secara mandiri oleh mahasiswa, dengan pengawasan dari administrator. Dengan fitur keamanan yang kuat dan antarmuka yang ramah pengguna, aplikasi ini siap digunakan di lingkungan akademik.

Untuk pengembangan lebih lanjut, dokumentasi ini dapat digunakan sebagai referensi untuk memahami struktur dan fungsi aplikasi, serta sebagai panduan untuk menambahkan fitur-fitur baru sesuai kebutuhan.