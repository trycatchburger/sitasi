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
  <section class="max-w-5xl mx-auto my-16 grid md:grid-cols-3 gap-6 text-center">
    <div class="bg-white p-8 rounded-2xl shadow hover:shadow-lg transition">
      <p class="text-4xl font-bold text-green-600">512</p>
      <p class="text-gray-600 mt-2">Skripsi Terdaftar</p>
    </div>
    <div class="bg-white p-8 rounded-2xl shadow hover:shadow-lg transition">
      <p class="text-4xl font-bold text-yellow-600">125</p>
      <p class="text-gray-600 mt-2">Tesis Terpublikasi</p>
    </div>
    <div class="bg-white p-8 rounded-2xl shadow hover:shadow-lg transition">
      <p class="text-4xl font-bold text-blue-600">80</p>
      <p class="text-gray-600 mt-2">Jurnal Ilmiah</p>
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

  <!-- Footer -->
  <footer class="mt-10 text-center text-sm text-gray-500 py-6 border-t">
    © 2025 STAIN Sultan Abdurrahman Kepulauan Riau — Repository Sistem
  </footer>


  <!-- Filter Box -->
  <div class="bg-white rounded-2xl shadow-lg p-4 mb-4 border border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800 mb-2 flex items-center">
      <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
      </svg>
      Saring
    </h2>

    <form method="GET" action="<?= url('submission/repository') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4">
      <input type="hidden" name="page" value="1">
      
      <!-- Search -->
      <div class="md:col-span-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? $_GET['search'] ?? '') ?>" placeholder="Search by title, author, or keywords..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>

      <!-- Year -->
      <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
        <select name="year" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
          <option value="">Semua Tahun</option>
          <?php
            $years = [];
            foreach ($submissions as $s) {
              $years[] = $s['tahun_publikasi'];
            }
            $years = array_unique($years);
            rsort($years);
            foreach ($years as $y):
          ?>
          <option value="<?= $y ?>" <?= ($y == ($_GET['year'] ?? '')) ? 'selected' : '' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Program -->
      <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
        <select name="program" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
          <option value="">Semua Program Studi</option>
          <?php
            $programs = [];
            foreach ($submissions as $s) {
              $programs[] = $s['program_studi'];
            }
            $programs = array_unique($programs);
            sort($programs);
            foreach ($programs as $p):
          ?>
          <option value="<?= htmlspecialchars($p) ?>" <?= ($p == ($_GET['program'] ?? '')) ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Buttons -->
      <div class="md:col-span-12 flex justify-end gap-3 pt-2">
        <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
          Filter
        </button>
        <a href="<?= url('submission/repository') ?>" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
          Bersihkan
        </a>
      </div>
    </form>
  </div>

  <!-- Theses List -->
  <?php if (empty($submissions)): ?>
    <div class="bg-white rounded-xl shadow p-10 text-center">
      <img src="https://www.svgrepo.com/show/327408/no-data.svg" alt="No Data" class="w-24 h-24 mx-auto mb-4 opacity-50">
      <p class="text-gray-500">Data Tidak Ditemukan</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 gap-6">
      <?php foreach ($submissions as $submission): ?>
        <?php
          $nameParts = explode(' ', htmlspecialchars($submission['nama_mahasiswa']));
          $lastName = end($nameParts); // Get the last part as the last name
          $firstNames = array_slice($nameParts, 0, -1); // Get all parts except the last
          $firstName = implode(' ', $firstNames); // Join the first/middle names
          $formattedName = $lastName . ', ' . $firstName;
          $titleLink = '<a href="' . url('submission/detail/' . $submission['id']) . '" class="text-green-700 hover:text-green-900 font-medium hover:underline">' . htmlspecialchars($submission['judul_skripsi']) . '</a>';
        ?>
        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-transform transform hover:-translate-y-1">
          <div class="text-lg font-semibold text-gray-800"><?= $formattedName ?> (<?= htmlspecialchars($submission['tahun_publikasi']) ?>)</div>
          <div class="mt-1 text-gray-700"><?= $titleLink ?></div>
          <div class="flex gap-3 mt-3 text-sm">
            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs font-medium">
              <?= htmlspecialchars($submission['program_studi']) ?>
            </span>
            <span class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-xs font-medium">
              NIM: <?= htmlspecialchars($submission['nim']) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  
  <!-- Pagination Controls -->
  <?php if (!empty($submissions) && $totalPages > 1): ?>
  <div class="mt-10 flex flex-col items-center">
    <div class="flex items-center justify-center space-x-2">
      <!-- Previous Button -->
      <?php if ($currentPage > 1): ?>
        <a href="<?= url('submission/repository?page=' . ($currentPage - 1) . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '') . ($program ? '&program=' . urlencode($program) : '')) ?>"
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
          <span class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Sebelumnya
          </span>
        </a>
      <?php else: ?>
        <span class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">
          <span class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Previous
          </span>
        </span>
      <?php endif; ?>
      
      <!-- Page Numbers -->
      <?php
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        // Show first page if not in range
        if ($startPage > 1) {
          echo '<a href="' . url('submission/repository?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '') . ($program ? '&program=' . urlencode($program) : '')) . '" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">1</a>';
          if ($startPage > 2) {
            echo '<span class="px-4 py-2 text-gray-500">...</span>';
          }
        }
        
        // Show page numbers in range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i == $currentPage) {
            echo '<span class="px-4 py-2 bg-green-600 text-white border border-green-600 rounded-lg">' . $i . '</span>';
          } else {
            echo '<a href="' . url('submission/repository?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '') . ($program ? '&program=' . urlencode($program) : '')) . '" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">' . $i . '</a>';
          }
        }
        
        // Show last page if not in range
        if ($endPage < $totalPages) {
          if ($endPage < $totalPages - 1) {
            echo '<span class="px-4 py-2 text-gray-500">...</span>';
          }
          echo '<a href="' . url('submission/repository?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '') . ($program ? '&program=' . urlencode($program) : '')) . '" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">' . $totalPages . '</a>';
        }
      ?>
      
      <!-- Next Button -->
      <?php if ($currentPage < $totalPages): ?>
        <a href="<?= url('submission/repository?page=' . ($currentPage + 1) . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '') . ($program ? '&program=' . urlencode($program) : '')) ?>"
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
          <span class="flex items-center">
            Next
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </span>
        </a>
      <?php else: ?>
        <span class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">
          <span class="flex items-center">
            Berikutnya
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </span>
        </span>
      <?php endif; ?>
    </div>
    
    <!-- Page Info -->
    <div class="mt-4 text-sm text-gray-600">
      Page <?= $currentPage ?> of <?= $totalPages ?> (Total: <?= $totalSubmissions ?> items)
    </div>
  </div>
  <?php endif; ?>
  
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