<?php ob_start(); ?>
<div class="container mx-auto px-1 py-1">

  <header class="text-center py-10 bg-blue-100/70">
    <h1 class="text-4xl font-extrabold text-blue-900 tracking-wide">REPOSITORY JURNAL</h1>
    <p class="mt-2 text-gray-700 max-w-2xl mx-auto">
      Kumpulan artikel jurnal ilmiah dosen dan mahasiswa STAIN Sultan Abdurrahman Kepulauan Riau.
    </p>

    <div class="mt-6 flex justify-center gap-4">
      <a href="<?= url('submission/repository_skripsi') ?>" class="px-5 py-2 bg-white border rounded-full hover:bg-blue-50">Skripsi</a>
      <a href="<?= url('submission/repository_tesis') ?>" class="px-5 py-2 bg-white border rounded-full hover:bg-blue-50">Tesis</a>
      <a href="<?= url('submission/repository_journal') ?>" class="px-5 py-2 bg-blue-600 text-white rounded-full shadow-md">Jurnal</a>
    </div>
  </header>

  
  <!-- Statistik -->
  <section class="max-w-5xl mx-auto mt-8 text-center text-gray-600">
    <p>📚 <strong><?= $totalSubmissions ?> Artikel Ilmiah</strong> terdaftar • Terbaru diunggah: <strong><?= $lastUpload ?></strong></p>
  </section>
  
  <!-- Filter Box -->
<!-- Filter Box -->
<div class="bg-white shadow-md border border-gray-100 rounded-2xl p-6 mt-6 mb-6"> <!-- tambahkan mb-6 -->
  <div class="flex items-center mb-4">
    <div class="w-9 h-9 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full mr-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-6 7v5l-6 3v-8L3 6V4z" />
      </svg>
    </div>
    <h2 class="text-lg font-semibold text-gray-800 tracking-wide">Filter Pencarian</h2>
  </div>

  <form method="GET" action="<?= url('submission/repository_journal') ?>"
  
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4">
    <input type="hidden" name="page" value="1">

    <!-- Search -->
    <div class="md:col-span-6 sm:col-span-2 col-span-1">
      <label class="block text-sm font-medium text-gray-600 mb-1">Cari</label>
      <input type="text" 
             name="search" 
             value="<?= htmlspecialchars($search ?? $_GET['search'] ?? '') ?>" 
             placeholder="🔍 Cari berdasarkan judul, penulis, atau kata kunci..." 
             class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150">
    </div>

    <!-- Year -->
    <div class="md:col-span-3 sm:col-span-1 col-span-1">
      <label class="block text-sm font-medium text-gray-600 mb-1">Tahun</label>
      <select name="year" 
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150">
        <option value="">Semua Tahun</option>
        <?php
          $years = [];
          foreach ($submissions as $s) $years[] = $s['tahun_publikasi'];
          $years = array_unique($years);
          rsort($years);
          foreach ($years as $y):
        ?>
        <option value="<?= $y ?>" <?= ($y == ($_GET['year'] ?? '')) ? 'selected' : '' ?>><?= $y ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Buttons -->
    <div class="md:col-span-3 sm:col-span-2 col-span-1 flex flex-col sm:flex-row md:justify-end items-stretch sm:items-end gap-3 pt-2">
      <button type="submit" 
              class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white font-medium rounded-xl shadow hover:bg-blue-700 transition transform hover:-translate-y-0.5 duration-200">
        Filter
      </button>
      <a href="<?= url('submission/repository_journal') ?>" 
         class="w-full sm:w-auto px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition">
        Bersihkan
      </a>
    </div>
  </form>
</div>

  <!-- Journals List -->
<div class="flex justify-between items-center mb-6">
  <h2 class="text-xl font-semibold text-gray-800">Daftar Jurnal</h2>

  <!-- Tombol Pilihan Tampilan -->
  <div class="flex gap-2">
    <button id="gridBtn" class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 active">🟦 Grid</button>
    <button id="listBtn" class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300">📃 List</button>
  </div>
</div>

<?php if (empty($submissions)): ?>
  <div class="bg-white rounded-xl shadow p-10 text-center">
    <img src="https://www.svgrepo.com/show/327408/no-data.svg" alt="No Data" class="w-24 h-24 mx-auto mb-4 opacity-50">
    <p class="text-gray-500">Data Tidak Ditemukan</p>
  </div>
<?php else: ?>
  <!-- Container utama -->
  <div id="submissionContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 transition-all duration-300">

    <?php foreach ($submissions as $submission): ?>
      <?php
        $nameParts = explode(' ', htmlspecialchars($submission['nama_mahasiswa']));
        $lastName = end($nameParts);
        $firstNames = array_slice($nameParts, 0, -1);
        $firstName = implode(' ', $firstNames);
        $formattedName = $lastName . ', ' . $firstName;

        $titleLink = '<a href="' . url('submission/detail/' . $submission['id']) . '" class="text-blue-70 hover:text-blue-900 font-medium hover:underline">' . htmlspecialchars($submission['judul_skripsi']) . '</a>';
      ?>

      <div class="submission-item bg-white rounded-xl shadow-md hover:shadow-lg transition-transform transform hover:-translate-y-1 overflow-hidden">
        <div class="p-5">
          <div class="text-lg font-semibold text-gray-800"><?= $formattedName ?> (<?= htmlspecialchars($submission['tahun_publikasi']) ?>)</div>
          <div class="mt-1 text-gray-70"><?= $titleLink ?></div>
          <div class="flex gap-3 mt-3 text-sm">
            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs font-medium">
              Journal
            </span>
          </div>
          <?php if (!empty($submission['abstract'])): ?>
            <div class="mt-3 text-sm text-gray-600 italic line-clamp-3">
              <?= htmlspecialchars(substr($submission['abstract'], 0, 300)) . (strlen($submission['abstract']) > 300 ? '...' : '') ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
  const gridBtn = document.getElementById('gridBtn');
  const listBtn = document.getElementById('listBtn');
  const container = document.getElementById('submissionContainer');

  gridBtn.addEventListener('click', () => {
    container.classList.remove('list-view');
    container.classList.add('grid', 'md:grid-cols-2', 'lg:grid-cols-3');
    gridBtn.classList.add('bg-blue-600', 'text-white');
    gridBtn.classList.remove('bg-gray-200', 'text-gray-700');
    listBtn.classList.add('bg-gray-200', 'text-gray-700');
    listBtn.classList.remove('bg-blue-600', 'text-white');
  });

  listBtn.addEventListener('click', () => {
    container.classList.add('list-view');
    container.classList.remove('grid', 'md:grid-cols-2', 'lg:grid-cols-3');
    gridBtn.classList.add('bg-gray-200', 'text-gray-700');
    gridBtn.classList.remove('bg-blue-600', 'text-white');
    listBtn.classList.add('bg-blue-600', 'text-white');
    listBtn.classList.remove('bg-gray-200', 'text-gray-700');
  });
</script>

<style>
  /* Tampilan list: susun horizontal */
  .list-view {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .list-view .submission-item {
    display: flex;
    flex-direction: row;
    align-items: center;
  }

  .list-view .submission-item .p-5 {
    flex: 1;
  }
</style>


  
  <!-- Pagination Controls -->
  <?php if (!empty($submissions) && $totalPages > 1): ?>
  <div class="mt-10 flex flex-col items-center">
    <div class="flex items-center justify-center space-x-2">
      <!-- Previous Button -->
      <?php if ($currentPage > 1): ?>
        <a href="<?= url('submission/repository_journal?page=' . ($currentPage - 1) . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '')) ?>"
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
          echo '<a href="' . url('submission/repository_journal?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '')) . '" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">1</a>';
          if ($startPage > 2) {
            echo '<span class="px-4 py-2 text-gray-500">...</span>';
          }
        }
        
        // Show page numbers in range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i == $currentPage) {
            echo '<span class="px-4 py-2 bg-blue-600 text-white border border-blue-600 rounded-lg">' . $i . '</span>';
          } else {
            echo '<a href="' . url('submission/repository_journal?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '')) . '" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">' . $i . '</a>';
          }
        }
        
        // Show last page if not in range
        if ($endPage < $totalPages) {
          if ($endPage < $totalPages - 1) {
            echo '<span class="px-4 py-2 text-gray-500">...</span>';
          }
          echo '<a href="' . url('submission/repository_journal?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '')) . '" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">' . $totalPages . '</a>';
        }
      ?>
      
      <!-- Next Button -->
      <?php if ($currentPage < $totalPages): ?>
        <a href="<?= url('submission/repository_journal?page=' . ($currentPage + 1) . ($search ? '&search=' . urlencode($search) : '') . ($year ? '&year=' . urlencode($year) : '')) ?>"
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
$title = 'Journal Repository | Portal Unggah Jurnal Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>