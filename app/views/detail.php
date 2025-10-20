<?php ob_start(); ?>
<div class="container mx-auto px-1 py-1">

  <!-- Back link -->
  <div class="mb-6">
    <a href="<?= url('submission/repository') ?>" class="inline-flex items-center text-green-700 hover:text-green-900 font-medium">
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to Repository
    </a>
 </div>

  <!-- Main container -->
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="p-8">

      <!-- Citation -->
      <div class="mb-8 bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-3">
          <h2 class="text-lg font-bold text-gray-800">Sitasi</h2>
          <button id="copyCitation" class="text-sm text-green-700 hover:text-green-900 font-medium flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            Salin
          </button>
        </div>
        <?php 
          $nameParts = explode(' ', htmlspecialchars($submission['nama_mahasiswa']));
          $firstName = $nameParts[0];
          $lastName = count($nameParts) > 1 ? end($nameParts) : $firstName;
          $formattedName = $lastName . ', ' . $firstName;
          $citation = $formattedName . '. (' . htmlspecialchars($submission['tahun_publikasi']) . '). ' . htmlspecialchars($submission['judul_skripsi']) . '. Skripsi, STAIN
           Sultan Abdurrahman Kepulauan Riau.';
        ?>
        <p id="citationText" class="text-gray-700 leading-relaxed"><?= $citation ?></p>
      </div>

      <!-- Content & Sidebar -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Main Content -->
        <div class="lg:col-span-2">
          <!-- Title -->
          <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($submission['judul_skripsi']) ?></h1>

          <!-- Info -->
          <div class="text-sm text-gray-600 mb-6 flex flex-wrap gap-4">
            <span>Oleh <strong><?= htmlspecialchars($submission['nama_mahasiswa']) ?></strong></span>
            <span><?= htmlspecialchars($submission['tahun_publikasi']) ?></span>
            <span><?= htmlspecialchars($submission['program_studi']) ?></span>
          </div>

          <!-- Files -->
          <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Files</h3>
            <?php
              // Take the first and second files
              $filteredFiles = array_slice($submission['files'], 0, 2);
            ?>
            <?php if (!empty($filteredFiles)): ?>
              <div class="space-y-4">
                <?php 
                  $fileIndex = 0;
                  foreach ($filteredFiles as $file): 
                  $fileIndex++;
                  // Determine display name based on file position
                  $displayName = htmlspecialchars($file['file_name']);
                  if ($fileIndex == 1) {
                    $displayName = "Cover";
                  } elseif ($fileIndex == 2) {
                    $displayName = "Bab1 - Daftar Pustaka";
                  }
                ?>
                  <a href="<?= url('file/publicView/' . $file['id']) ?>" target="_blank"
                    class="flex items-center gap-4 p-4 bg-white border border-gray-200 rounded-lg hover:shadow transition">
                    <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-full">
                      <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div class="flex-grow">
                      <p class="font-medium text-gray-900"><?= $displayName ?></p>
                      <p class="text-xs text-gray-500">Klik untuk melihat</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-gray-500">Tidak ada data yang ditemukan.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
          <!-- Thesis Details -->
          <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Details Skripsi</h3>
            <ul class="text-sm text-gray-700 space-y-3">
              <li><span class="block text-gray-500">Nama Mahasiswa</span><strong><?= htmlspecialchars($submission['nama_mahasiswa']) ?></strong></li>
              <li><span class="block text-gray-500">NIM</span><strong><?= htmlspecialchars($submission['nim']) ?></strong></li>
              <li><span class="block text-gray-500">Dosen Pembimbing 1</span><strong><?= htmlspecialchars($submission['dosen1']) ?></strong></li>
              <li><span class="block text-gray-500">Dosen Pembimbing 2</span><strong><?= htmlspecialchars($submission['dosen2']) ?></strong></li>
              <li><span class="block text-gray-500">Program Studi</span><strong><?= htmlspecialchars($submission['program_studi']) ?></strong></li>
              <li><span class="block text-gray-500">Tahun Publikasi</span><strong><?= htmlspecialchars($submission['tahun_publikasi']) ?></strong></li>
              <li><span class="block text-gray-500">Tanggal Unggah</span><strong><?= format_datetime($submission['created_at'], 'F j, Y') ?></strong></li>
            </ul>
          </div>

          
        </aside>
      </div>
    </div>
  </div>
</div>

<!-- Copy to clipboard script -->
<script>
  document.getElementById('copyCitation').addEventListener('click', function () {
    const text = document.getElementById('citationText').innerText;
    navigator.clipboard.writeText(text).then(() => {
      const btn = document.getElementById('copyCitation');
      btn.innerHTML = '✔ Copied!';
      setTimeout(() => {
        btn.innerHTML = `<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 0 002 2z"/></svg> Copy`;
      }, 2000);
    });
  });
</script>

<?php
$title = 'Detail Skripsi | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>