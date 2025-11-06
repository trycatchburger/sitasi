<?php ob_start(); ?>

<!-- Main Content -->
<main id="main-content">
  <div class="px-1 py-1 w-full">
    <div class="mb-4">
      <h1 class="text-2xl font-bold text-gray-800">Verifikasi Skripsi</h1>
      <p class="text-gray-600 mt-1">Kelola dan tinjau pengajuan skripsi mahasiswa</p>
    </div>
    
    <?php if (isset($queryStats) && $queryStats): ?>
      <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold text-blue-800 mb-2">Query Performance Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white p-3 rounded shadow">
            <div class="text-sm text-gray-600">Total Queries</div>
            <div class="text-2xl font-bold text-blue-600"><?= $queryStats['total_queries'] ?></div>
          </div>
          <div class="bg-white p-3 rounded shadow">
            <div class="text-sm text-gray-600">Avg. Execution Time</div>
            <div class="text-2xl font-bold text-blue-600"><?= isset($queryStats['average_time']) ? number_format($queryStats['average_time'], 4) : 'N/A' ?>s</div>
          </div>
          <div class="bg-white p-3 rounded shadow">
            <div class="text-sm text-gray-600">Slow Queries</div>
            <div class="text-2xl font-bold text-blue-600"><?= $queryStats['slow_queries'] ?? 0 ?></div>
          </div>
        <?php if (($queryStats['slow_queries'] ?? 0) > 0): ?>
          <div class="mt-2 text-sm text-yellow-700">
            <strong>Warning:</strong> <?= $queryStats['slow_queries'] ?> slow queries detected. Check error logs for details.
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    
    <div class="mb-6 flex flex-wrap items-center gap-4">
      <div class="flex items-center space-x-4">
        <span class="text-gray-700">Filter:</span>
        <button id="showAllBtn" class="btn btn-secondary btn-sm <?= (!empty($showAll) && !isset($_GET['type'])) ? 'bg-blue-600 text-white' : '' ?>">Tampilkan Semua Pengajuan</button>
        <button id="showPendingBtn" class="btn btn-secondary btn-sm <?= (empty($showAll) && !isset($_GET['type'])) ? 'bg-blue-600 text-white' : '' ?>">Tampilkan Hanya yang Belum Diverifikasi (Default)</button>
        <button id="showJournalBtn" class="btn btn-secondary btn-sm <?= (isset($_GET['type']) && $_GET['type'] === 'journal') ? 'bg-blue-600 text-white' : '' ?>">Tampilkan Pengajuan Jurnal Saja</button>
      </div>
      
      <!-- Search Form -->
      <div class="flex items-center ml-auto">
        <form method="GET" action="<?= url('admin/management_file') ?>" class="flex">
          <?php if (isset($_GET['type']) && $_GET['type'] === 'journal'): ?>
            <input type="hidden" name="type" value="journal">
          <?php else: ?>
            <input type="hidden" name="show" value="<?= isset($_GET['show']) ? htmlspecialchars($_GET['show']) : (isset($showAll) && $showAll ? 'all' : 'pending') ?>">
          <?php endif; ?>
          <input type="hidden" name="page" value="1"> <!-- Reset to first page when searching -->
          <input type="text"
                 name="search"
                 placeholder="Cari nama mahasiswa, judul skripsi, atau NIM..."
                 value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                 class="border border-gray-300 rounded-l px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 014 0z"></path>
            </svg>
          </button>
          <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
            <a href="<?= url('admin/management_file') ?>?<?= isset($_GET['show']) ? 'show=' . htmlspecialchars($_GET['show']) : (isset($showAll) && $showAll ? 'show=all' : '') ?>&page=1"
               class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </a>
          <?php endif; ?>
        </form>
      </div>
    </div>
    
    <div class="mb-6 flex flex-wrap gap-2">
      <a href="<?= url('file/downloadAll') ?>" class="btn btn-primary text-white">
        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        Unduh Semua Berkas (Terorganisir)
      </a>

    </div>
    
    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 max-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-50 uppercase tracking-wider">Tipe</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mahasiswa</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Skripsi</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berkas</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($submissions)): ?>
              <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                  <?php if (isset($search) && !empty($search)): ?>
                    Tidak ada pengajuan ditemukan untuk pencarian: "<strong><?= htmlspecialchars($search) ?></strong>"
                  <?php else: ?>
                    Tidak ada pengajuan ditemukan
                  <?php endif; ?>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($submissions as $submission): ?>
                <tr class="hover:bg-gray-50 <?= isset($submission['is_resubmission']) && $submission['is_resubmission'] ? 'bg-green-50 border-l-4 border-l-green-40' : '' ?>">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php
                      $submission_type = $submission['submission_type'] ?? 'bachelor'; // Default to bachelor if not set
                      $type_label = '';
                      $type_color = 'bg-gray-100 text-gray-800'; // Default color
                      
                      switch ($submission_type) {
                        case 'bachelor':
                          $type_label = 'Skripsi';
                          $type_color = 'bg-blue-100 text-blue-800';
                          break;
                        case 'master':
                          $type_label = 'Tesis';
                          $type_color = 'bg-purple-100 text-purple-800';
                          break;
                        case 'journal':
                          $type_label = 'Jurnal';
                          $type_color = 'bg-green-100 text-green-800';
                          break;
                        default:
                          $type_label = ucfirst($submission_type);
                          $type_color = 'bg-gray-100 text-gray-800';
                          break;
                      }
                    ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $type_color ?>">
                      <?= $type_label ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900 flex items-center">
                      <?= htmlspecialchars($submission['nama_mahasiswa']) ?>
                      <?php if (isset($submission['is_resubmission']) && $submission['is_resubmission']): ?>
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800" title="This submission has been resubmitted">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.01 8.001 0 004.582 9m0 0H9m1 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                          </svg>
                          Pengajuan Kembali
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="text-sm text-gray-500"><?= htmlspecialchars($submission['nim']) ?></div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900 max-w-[250px] truncate" title="<?= htmlspecialchars($submission['judul_skripsi']) ?>"><?= htmlspecialchars($submission['judul_skripsi']) ?></div>
                  </td>
                  <td class="px-6 py-4">
                    <?php if (!empty($submission['files'])): ?>
                      <div class="flex flex-col gap-1">
                        <?php foreach ($submission['files'] as $file): ?>
                          <div class="flex flex-col gap-1">
                            <a href="<?= url('file/view/' . $file['id']) ?>" target="_blank" class="btn btn-secondary btn-sm w-full text-center">
                              <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                              </svg>
                              View
                            </a>
                            
                            <?php
                              $fileExtension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                              if (in_array($fileExtension, ['doc', 'docx'])):
                            ?>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <span class="text-gray-400 text-sm">No files</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php if (isset($submission['is_resubmission']) && $submission['is_resubmission'] && $submission['created_at'] !== $submission['updated_at']): ?>
                      <div class="flex flex-col">
                        <span class="text-xs text-gray-500">Dibuat: <?= format_datetime($submission['created_at']) ?></span>
                        <span class="text-xs text-blue-60 font-medium">Diperbarui: <?= format_datetime($submission['updated_at']) ?></span>
                      </div>
                    <?php else: ?>
                      <?= format_datetime($submission['created_at']) ?>
                      <div class="text-xs text-gray-400"><?= format_time($submission['created_at']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <!-- File upload form for converted files -->
                    <div class="mt-2 pt-2 border-t border-gray-200">
                      <form action="<?= url('file/uploadConvertedFile/' . $submission['id']) ?>" method="POST" enctype="multipart/form-data" class="flex flex-col gap-1" onsubmit="return confirm('Are you sure you want to upload this converted file? This will add the file to the existing submission.')">
                        <?= csrf_field() ?>
                        <input type="file" name="converted_file" accept=".pdf,.doc,.docx" class="text-xs mb-1" required>
                        <button type="submit" class="btn btn-success btn-sm text-xs py-1 px-2">Upload Converted</button>
                      </form>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a href="<?= url('file/download/' . $submission['id']) ?>" class="btn btn-secondary btn-sm">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4l-4 4m0 0l-4-4m4 4V4"></path>
                      </svg>
                      ZIP
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="mt-6 flex justify-center">
          <nav class="inline-flex rounded-md shadow">
            <?php if ($currentPage > 1): ?>
              <a href="<?= url('admin/management_file') ?>?page=<?= $currentPage - 1 ?><?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                Previous
              </a>
            <?php endif; ?>
            
            <?php
              // Show first page
              if ($currentPage > 3):
            ?>
              <a href="<?= url('admin/management_file') ?>?page=1<?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                1
              </a>
              <?php if ($currentPage > 4): ?>
                <span class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500">
                  ...
                </span>
              <?php endif; ?>
            <?php endif; ?>
            
            <?php
              // Show pages around current page
              $start = max(1, $currentPage - 2);
              $end = min($totalPages, $currentPage + 2);
              for ($i = $start; $i <= $end; $i++):
            ?>
              <a href="<?= url('admin/management_file') ?>?page=<?= $i ?><?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium <?= $i == $currentPage ? 'text-blue-600 border-blue-600' : 'text-gray-500 hover:bg-gray-50' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
            
            <?php
              // Show last page
              if ($currentPage < $totalPages - 2):
                if ($currentPage < $totalPages - 3):
            ?>
                  <span class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500">
                    ...
                  </span>
                <?php endif; ?>
                <a href="<?= url('admin/management_file') ?>?page=<?= $totalPages ?><?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                  <?= $totalPages ?>
                </a>
              <?php endif; ?>
            
            <?php if ($currentPage < $totalPages): ?>
              <a href="<?= url('admin/management_file') ?>?page=<?= $currentPage + 1 ?><?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                Next
              </a>
            <?php endif; ?>
          </nav>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php
$title = 'Management File - University Thesis Submission System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>

<style>
  .modal-open {
    overflow: hidden;
  }
</style>

<script>
// Handle filter buttons
document.getElementById('showAllBtn')?.addEventListener('click', function() {
  // Redirect to the same page with show=all parameter
  const url = new URL(window.location);
  url.searchParams.set('show', 'all');
  url.searchParams.delete('type'); // Remove type parameter when showing all
  url.searchParams.delete('page'); // Reset to first page when changing filter
  url.searchParams.delete('search'); // Also clear search when changing filter
  window.location.href = url.toString();
});

document.getElementById('showPendingBtn')?.addEventListener('click', function() {
  // Redirect to the same page without show parameter (default is pending only)
  const url = new URL(window.location);
  url.searchParams.delete('show');
  url.searchParams.delete('type'); // Remove type parameter when showing pending
  url.searchParams.delete('page'); // Reset to first page when changing filter
  url.searchParams.delete('search'); // Also clear search when changing filter
  window.location.href = url.toString();
});

// Handle journal filter button
document.getElementById('showJournalBtn')?.addEventListener('click', function() {
  // Redirect to the same page with type=journal parameter
  const url = new URL(window.location);
  url.searchParams.set('type', 'journal');
  url.searchParams.delete('show'); // Remove show parameter when showing journals
  url.searchParams.delete('page'); // Reset to first page when changing filter
  url.searchParams.delete('search'); // Also clear search when changing filter
  window.location.href = url.toString();
});
</script>