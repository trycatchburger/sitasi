<?php ob_start(); ?>
<div class="container mx-auto px-1 py-1">

  <!-- Header -->
  <header class="text-center py-10 bg-green-100/70 border-b">
    <h1 class="text-4xl md:text-5xl font-extrabold text-green-900 tracking-wide">
      REPOSITORY DIGITAL
    </h1>
    <p class="mt-3 text-gray-700 max-w-2xl mx-auto text-lg">
      Akses koleksi karya ilmiah mahasiswa dan dosen STAIN Sultan Abdurrahman Kepulauan Riau.  
      Skripsi, Tesis, dan Jurnal tersedia untuk penelitian dan pembelajaran.
    </p>
  </header>

    <!-- Hero Section -->
  <section class="text-center py-16 px-4">
    <div class="max-w-3xl mx-auto">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135755.png" alt="Repository" class="mx-auto w-32 h-32 opacity-80 mb-6">
      <h2 class="text-3xl font-semibold text-green-800 mb-3">Selamat Datang di Sistem Repository STAIN SAR Kepri</h2>
      <p class="text-gray-600 mb-8">
        Temukan karya ilmiah terbaik dari mahasiswa dan dosen kami.  
        Mulai dari skripsi, tesis, hingga jurnal ilmiah — semua tersedia dalam format digital yang mudah diakses.
      </p>
      <div class="flex flex-wrap justify-center gap-4">
        <a href="<?= url('submission/repository_skripsi') ?>" class="px-6 py-3 bg-green-600 text-white rounded-full shadow hover:bg-green-700 transition">📘 Skripsi</a>
        <a href="<?= url('submission/repository_tesis') ?>" class="px-6 py-3 bg-yellow-500 text-white rounded-full shadow hover:bg-yellow-600 transition">📗 Tesis</a>
        <a href="<?= url('submission/repository_journal') ?>" class="px-6 py-3 bg-blue-600 text-white rounded-full shadow hover:bg-blue-700 transition">📙 Jurnal</a>
      </div>
    </div>
  </section>

  <!-- Statistik Ringkas -->
  <?php
  // Ensure stats array exists with default values
  $stats = $stats ?? ['skripsi' => 0, 'tesis' => 0, 'jurnal' => 0];
  ?>
  <section class="max-w-5xl mx-auto my-16 grid md:grid-cols-3 gap-6 text-center" aria-labelledby="stats-heading">
    <h2 id="stats-heading" class="sr-only">Repository Statistics</h2>
    <div class="bg-white p-8 rounded-2xl shadow hover:shadow-lg transition" role="statistic" aria-label="Skripsi Terdaftar: <?= (int)($stats['skripsi'] ?? 0) ?>">
      <p class="text-4xl font-bold text-green-60" aria-hidden="true"><?= htmlspecialchars($stats['skripsi'] ?? 0) ?></p>
      <p class="text-gray-600 mt-2 font-medium">Skripsi Terdaftar</p>
    </div>
    <div class="bg-white p-8 rounded-2xl shadow hover:shadow-lg transition" role="statistic" aria-label="Tesis Terpublikasi: <?= (int)($stats['tesis'] ?? 0) ?>">
      <p class="text-4xl font-bold text-yellow-600" aria-hidden="true"><?= htmlspecialchars($stats['tesis'] ?? 0) ?></p>
      <p class="text-gray-600 mt-2 font-medium">Tesis Terpublikasi</p>
    </div>
    <div class="bg-white p-8 rounded-2xl shadow hover:shadow-lg transition" role="statistic" aria-label="Jurnal Ilmiah: <?= (int)($stats['jurnal'] ?? 0) ?>">
      <p class="text-4xl font-bold text-blue-600" aria-hidden="true"><?= htmlspecialchars($stats['jurnal'] ?? 0) ?></p>
      <p class="text-gray-600 mt-2 font-medium">Jurnal Ilmiah</p>
    </div>
  </section>

  <!-- Tentang Repository -->
  <section class="bg-green-50 py-16">
    <div class="max-w-5xl mx-auto text-center px-6">
      <h2 class="text-2xl font-bold text-green-800 mb-4">Tentang Repository</h2>
      <p class="text-gray-700 max-w-3xl mx-auto leading-relaxed">
        Repository digital ini merupakan pusat penyimpanan karya ilmiah mahasiswa dan dosen 
        STAIN Sultan Abdurrahman Kepulauan Riau.  
        Tujuannya adalah untuk memfasilitasi diseminasi pengetahuan, meningkatkan aksesibilitas informasi, 
        serta mendukung pengembangan akademik dan penelitian berbasis digital.
      </p>
    </div>
  </section>

  <!-- Kontak / Informasi -->
  <section class="text-center py-12">
    <h2 class="text-xl font-bold text-green-800 mb-3">Hubungi Kami</h2>
    <p class="text-gray-600">
      📍 Kampus STAIN SAR Kepri, Tanjungpinang<br>
      📧 repository@stainsar.ac.id • ☎️ (0771) 123456
    </p>
  </section>
  
  <!-- Optional Scroll to Top Button -->
  <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 bg-green-600 hover:bg-green-700 text-white p-3 rounded-full shadow-lg" title="Back to Top">
    ↑
 </button>


</div>



<?php
$title = 'Repository | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>