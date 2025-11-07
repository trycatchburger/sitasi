
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
            <div class="text-2xl font-bold text-blue-60"><?= $queryStats['total_queries'] ?></div>
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
        <select id="filterSelect" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <option value="pending" <?= (empty($showAll) && !isset($_GET['type'])) ? 'selected' : '' ?>>Tampilkan Hanya yang Belum Diverifikasi (Default)</option>
          <option value="all" <?= (!empty($showAll) && !isset($_GET['type'])) ? 'selected' : '' ?>>Tampilkan Semua Pengajuan</option>
          <option value="journal" <?= (isset($_GET['type']) && $_GET['type'] === 'journal') ? 'selected' : '' ?>>Tampilkan Pengajuan Jurnal Saja</option>
        </select>
      </div>
      
      <!-- Search Form -->
      <div class="flex items-center ml-auto">
        <form method="GET" action="<?= url('admin/dashboard') ?>" class="flex">
          <?php if (isset($_GET['type']) && $_GET['type'] === 'journal'): ?>
            <input type="hidden" name="type" value="journal">
          <?php else: ?>
            <input type="hidden" name="show" value="<?= isset($_GET['show']) ? htmlspecialchars($_GET['show']) : (isset($showAll) && $showAll ? 'all' : 'pending') ?>">
          <?php endif; ?>
          <?php if (isset($_GET['sort'])): ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
          <?php endif; ?>
          <?php if (isset($_GET['order'])): ?>
            <input type="hidden" name="order" value="<?= htmlspecialchars($_GET['order']) ?>">
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
            <a href="<?= url('admin/dashboard') ?>?<?= isset($_GET['show']) ? 'show=' . htmlspecialchars($_GET['show']) : (isset($showAll) && $showAll ? 'show=all' : '') ?>&page=1<?= isset($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= isset($order) ? '&order=' . htmlspecialchars($order) : '' ?>"
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
      <a href="<?= url('file/downloadAll') ?>" class="btn btn-primary text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium rounded-md transition duration-300 ease-in-out transform hover:scale-105 py-2 px-4 flex items-center">
        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 0 003-3v-1m-4-4l-4 4m0 0l-4m4 4V4"></path>
        </svg>
        Unduh Semua Berkas (Terorganisir)
      </a>

    </div>
    
    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 max-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-50 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('type', '<?= isset($_GET['sort']) && $_GET['sort'] === 'type' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['type']) ? $_GET['type'] : (isset($showAll) && $showAll ? 'all' : 'pending') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Tipe
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'type'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('student_name', '<?= isset($_GET['sort']) && $_GET['sort'] === 'student_name' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['type']) ? $_GET['type'] : (isset($showAll) && $showAll ? 'all' : 'pending') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Nama Mahasiswa
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'student_name'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('title', '<?= isset($_GET['sort']) && $_GET['sort'] === 'title' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['type']) ? $_GET['type'] : (isset($showAll) && $showAll ? 'all' : 'pending') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Judul Skripsi
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'title'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berkas</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('status', '<?= isset($_GET['sort']) && $_GET['sort'] === 'status' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['type']) ? $_GET['type'] : (isset($showAll) && $showAll ? 'all' : 'pending') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Status
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'status'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('date', '<?= isset($_GET['sort']) && $_GET['sort'] === 'date' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['type']) ? $_GET['type'] : (isset($showAll) && $showAll ? 'all' : 'pending') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Tanggal Pengajuan
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'date'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
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
                      <?php
                        // Limit to first 4 files to show in 2x2 grid
                        $filesToShow = array_slice($submission['files'], 0, 4);
                        $fileCount = count($filesToShow);
                      ?>
                      <div class="flex flex-wrap gap-1 max-w-xs">
                        <?php foreach ($filesToShow as $file): ?>
                          <?php
                            // Extract the file label from the file name to show what type of file it is
                            $fileNameWithoutExtension = pathinfo($file['file_name'], PATHINFO_FILENAME);
                            $fileNameParts = explode('.', $fileNameWithoutExtension);
                            $fileLabel = '';
                            
                            if (count($fileNameParts) >= 1) {
                              $fileLabel = $fileNameParts[0]; // First part is the label
                              
                              // Map the file label to specific labels for better readability
                              if (stripos($fileLabel, 'cover') !== false) {
                                  $displayLabel = 'Cover';
                              } else if (stripos($fileLabel, 'bab1') !== false || stripos($fileLabel, 'transkrip') !== false) {
                                  $displayLabel = 'Bab 1';
                              } else if (stripos($fileLabel, 'bab2') !== false || stripos($fileLabel, 'toefl') !== false) {
                                  $displayLabel = 'Bab 2';
                              } else if (stripos($fileLabel, 'persetujuan') !== false || stripos($fileLabel, 'doc') !== false) {
                                  $displayLabel = 'Full Version';
                              } else if (stripos($fileLabel, 'converted') !== false) {
                                  $displayLabel = 'Converted';
                              } else {
                                  $displayLabel = ucfirst(str_replace('_', ' ', $fileLabel));
                              }
                            } else {
                              $displayLabel = 'File';
                            }
                          ?>
                          <div class="flex flex-col items-center text-center min-w-[80px] max-w-[90px]">
                            <div class="text-xs font-medium text-gray-700 mb-1 truncate w-full" title="<?= htmlspecialchars($displayLabel) ?>"><?= htmlspecialchars($displayLabel) ?></div>
                            <a href="<?= url('file/view/' . $file['id']) ?>" target="_blank" class="btn btn-secondary btn-sm w-full text-center bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-md transition duration-300 ease-in-out py-1 px-2 text-xs">
                              <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 0 012-2h5.586a1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 0 01-2 2z"></path>
                              </svg>
                              View
                            </a>
                          </div>
                        <?php endforeach; ?>
                        <?php if (count($submission['files']) > 4): ?>
                          <div class="flex items-center justify-center min-w-[80px] max-w-[90px] text-xs text-gray-500">
                            +<?= count($submission['files']) - 4 ?> more
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <span class="text-gray-400 text-sm">No files</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php
                      $status = htmlspecialchars($submission['status']);
                      $badge_color = 'bg-gray-100 text-gray-800'; // Default for Pending
                      if ($status === 'Diterima') {
                        $badge_color = 'bg-green-100 text-green-800';
                      } elseif ($status === 'Ditolak') {
                        $badge_color = 'bg-red-100 text-red-800';
                      } elseif ($status === 'Digantikan') {
                        $badge_color = 'bg-yellow-100 text-yellow-800';
                      }
                    ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge_color ?>">
                      <?= $status ?>
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <form action="<?= url('admin/updateStatus') ?>" method="POST" class="flex flex-col gap-1">
                      
                      <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                      <?= csrf_field() ?>
                      <input type="text" name="serial_number" placeholder="No. Surat" class="border rounded px-1 py-1 text-xs" value="<?= htmlspecialchars($submission['serial_number'] ?? '') ?>">
                      <select name="status" class="border rounded px-1 py-1 text-xs">
                        <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Diterima" <?= $status === 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                        <option value="Ditolak" <?= $status === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                      </select>
                      <textarea name="reason" placeholder="Alasan" class="border rounded px-1 py-1 text-xs"><?= htmlspecialchars($submission['keterangan'] ?? '') ?></textarea>
                      <button type="submit" class="btn btn-primary btn-sm text-xs py-1 px-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-md transition duration-300 ease-in-out transform hover:scale-105">
                        Update
                      </button>
                    </form>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php if (isset($submission['is_resubmission']) && $submission['is_resubmission'] && $submission['created_at'] !== $submission['updated_at']): ?>
                      <div class="flex flex-col">
                        <span class="text-xs text-gray-50">Dibuat: <?= format_datetime($submission['created_at']) ?></span>
                        <span class="text-xs text-blue-60 font-medium">Diperbarui: <?= format_datetime($submission['updated_at']) ?></span>
                      </div>
                    <?php else: ?>
                      <?= format_datetime($submission['created_at']) ?>
                      <div class="text-xs text-gray-400"><?= format_time($submission['created_at']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a href="<?= url('file/download/' . $submission['id']) ?>" class="btn btn-secondary btn-sm bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium rounded-md transition duration-300 ease-in-out transform hover:scale-105 py-1 px-2 text-xs flex items-center">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 0 003-3v-1m-4l-4 4m0 0l-4-4m4 4V4"></path>
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
              <a href="<?= url('admin/dashboard') ?>?page=<?= $currentPage - 1 ?><?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= isset($order) ? '&order=' . htmlspecialchars($order) : '' ?>" class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                Previous
              </a>
            <?php endif; ?>
            
            <?php
              // Show first page
              if ($currentPage > 3):
            ?>
              <a href="<?= url('admin/dashboard') ?>?page=1<?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= isset($order) ? '&order=' . htmlspecialchars($order) : '' ?>" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                1
              </a>
              <?php if ($currentPage > 4): ?>
                <span class="px-3 py-2 border-t border-b border-gray-30 bg-white text-sm font-medium text-gray-500">
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
              <a href="<?= url('admin/dashboard') ?>?page=<?= $i ?><?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= isset($order) ? '&order=' . htmlspecialchars($order) : '' ?>" class="px-3 py-2 border-t border-b border-gray-30 bg-white text-sm font-medium <?= $i == $currentPage ? 'text-blue-600 border-blue-600' : 'text-gray-500 hover:bg-gray-50' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
            
            <?php
              // Show last page
              if ($currentPage < $totalPages - 2):
                if ($currentPage < $totalPages - 3):
            ?>
                  <span class="px-3 py-2 border-t border-b border-gray-30 bg-white text-sm font-medium text-gray-500">
                    ...
                  </span>
                <?php endif; ?>
                <a href="<?= url('admin/dashboard') ?>?page=<?= $totalPages ?><?= isset($_GET['type']) && $_GET['type'] === 'journal' ? '&type=journal' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= isset($order) ? '&order=' . htmlspecialchars($order) : '' ?>" class="px-3 py-2 border-t border-b border-gray-30 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                  <?= $totalPages ?>
                </a>
              <?php endif; ?>
            
            <?php if ($currentPage < $totalPages): ?>
            <?php endif; ?>
          </nav>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php
$title = 'Admin Dashboard - University Thesis Submission System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>

<!-- Success Modal -->
<div id="successModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div class="absolute inset-0 bg-black opacity-50"></div>
  <div class="relative bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-green-600">Success!</h3>
      <button id="closeModal" class="text-gray-50 hover:text-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <p class="text-gray-700">Status updated and email sent successfully!</p>
    <div class="mt-6">
      <button id="okButton" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded">
        OK
      </button>
    </div>
  </div>
</div>


<style>
  .modal-open {
    overflow: hidden;
  }
</style>

<script>
// Handle status update form submissions via AJAX
document.querySelectorAll('form[action="<?= url('admin/updateStatus') ?>"]').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.textContent = 'Updating...';
    
    fetch(this.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => {
       if (!response.ok) {
         // Log the response status and text for debugging
         return response.text().then(text => {
           console.error('Server error response:', response.status, text);
           throw new Error(`Network response was not ok: ${response.status} - ${text}`);
         });
       }
       return response.json();
     })
    .then(data => {
      if (data.success) {
        // Get the row for this submission
        const row = this.closest('tr');
        
        // Update the status badge
        const statusCell = row.querySelector('td:nth-child(6)'); // Status column (now 6th column after adding type column at the beginning)
        const statusSelect = this.querySelector('select[name="status"]');
        const newStatus = statusSelect.value;
        
        // Determine badge color based on new status
        let badgeColor = 'bg-gray-100 text-gray-800'; // Default for Pending
        if (newStatus === 'Diterima') {
          badgeColor = 'bg-green-100 text-green-800';
        } else if (newStatus === 'Ditolak') {
          badgeColor = 'bg-red-100 text-red-800';
        } else if (newStatus === 'Digantikan') {
          badgeColor = 'bg-yellow-100 text-yellow-800';
        }
        
        // Update the status badge HTML
        statusCell.innerHTML = `
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeColor}">
            ${newStatus}
          </span>
        `;
        
        // Preserve the submission type column by getting the original value from the form or server response
        // The submission type doesn't change, so we don't need to update it
        
        // Get the input values from the form that was submitted
        const reasonInput = this.querySelector('textarea[name="reason"]');
        const serialInput = this.querySelector('input[name="serial_number"]');
        
        // Show success modal with server message
        document.getElementById('successModal').classList.remove('hidden');
        document.body.classList.add('modal-open');
        
        // Update modal content with server message
        const modalContent = document.querySelector('#successModal p');
        if (modalContent) {
          modalContent.textContent = data.message || 'Status updated and email sent successfully!';
        }
        
        // Also update the values displayed in the form in the table cell
        const reasonCell = this.querySelector('td:nth-child(8)'); // Reason column (8th column based on the table structure after adding type column at the beginning)
        if (reasonCell) {
            const reasonField = reasonCell.querySelector('textarea[name="reason"]');
            if (reasonField) {
              reasonField.value = reasonInput.value;
            }
            
            const serialField = reasonCell.querySelector('input[name="serial_number"]');
            if (serialField) {
              serialField.value = serialInput.value;
            }
        }
        
        // After successful update, the page might need to be refreshed to reflect changes in the search results
        // Only refresh if the modal is shown to indicate success and we're on a search results page
        if (data && data.success && !document.getElementById('successModal').classList.contains('hidden')) {
            // Add a small delay to allow the success message to be seen
            setTimeout(() => {
                // Check if we're on a search results page and refresh to update the display
                if (window.location.search.includes('search=')) {
                    window.location.reload();
                } else if (window.location.search.includes('show=') || window.location.search.includes('type=')) {
                    // Also refresh if we're on a filtered view (show=all, show=pending, or type=journal)
                    window.location.reload();
                }
            }, 500); // 0.5 second delay to allow user to see success message
        }
      } else {
        // Handle error from server
        alert(data.message || 'An error occurred while updating the status.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      // Check if this is a JSON parsing error or network error
      // In most cases, we still want to show a user-friendly message
      alert('An error occurred while updating the status. Error details: ' + error.message + '. Please refresh the page to check the current status.');
    })
    .finally(() => {
      // Reset button state
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    });
  });
});

// Handle modal close buttons
document.getElementById('closeModal')?.addEventListener('click', function() {
  document.getElementById('successModal').classList.add('hidden');
  document.body.classList.remove('modal-open');
});

// Function to handle sorting
function sortTable(column, order, showType, search) {
  let url = new URL(window.location);
  
  // Set sorting parameters
  url.searchParams.set('sort', column);
  url.searchParams.set('order', order);
  
  // Preserve existing parameters
  if(showType) {
    if(showType === 'all' || showType === 'pending') {
      url.searchParams.set('show', showType);
    } else if(showType === 'journal') {
      url.searchParams.set('type', showType);
    }
  }
  
  if(search) {
    url.searchParams.set('search', search);
  }
  
  // Reset to first page when sorting
  url.searchParams.set('page', 1);
  
  window.location.href = url.toString();
}
document.getElementById('okButton')?.addEventListener('click', function() {
  document.getElementById('successModal').classList.add('hidden');
  document.body.classList.remove('modal-open');
});
// Handle filter dropdown
document.getElementById('filterSelect')?.addEventListener('change', function() {
  const selectedValue = this.value;
  const url = new URL(window.location);
  
  // Preserve sorting parameters
 const sortParam = url.searchParams.get('sort');
  const orderParam = url.searchParams.get('order');
  
  // Clear existing filter parameters
  url.searchParams.delete('show');
  url.searchParams.delete('type');
  url.searchParams.delete('page'); // Reset to first page when changing filter
 url.searchParams.delete('search'); // Also clear search when changing filter
  
  // Set new filter parameter based on selection
 if (selectedValue === 'all') {
    url.searchParams.set('show', 'all');
 } else if (selectedValue === 'journal') {
    url.searchParams.set('type', 'journal');
  }
  // For 'pending' option, we don't need to set any parameter as it's the default
  
  // Re-add sorting parameters if they existed
  if(sortParam) {
    url.searchParams.set('sort', sortParam);
  }
  if(orderParam) {
    url.searchParams.set('order', orderParam);
  }
  
  window.location.href = url.toString();
});
</script>