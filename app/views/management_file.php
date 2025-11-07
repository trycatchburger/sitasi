<?php ob_start(); ?>

<!-- Main Content -->
<main id="main-content">
  <div class="px-1 py-1 w-full">
    <div class="mb-4">
      <h1 class="text-2xl font-bold text-gray-800">Management File</h1>
      <p class="text-gray-600 mt-1">Halaman khusus untuk teknisi IT untuk mengelola file yang diunggah</p>
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
        <div class="relative">
          <select id="filterSelect" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 pl-3 pr-10 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
            <option value="all" <?= (isset($_GET['show']) && $_GET['show'] === 'all') ? 'selected' : (!(isset($_GET['show']) || isset($_GET['converted'])) ? 'selected' : '') ?>>Tampilkan semua</option>
            <option value="unconverted" <?= (isset($_GET['converted']) && $_GET['converted'] === 'unconverted') ? 'selected' : '' ?>>Tampilkan yang belum diconvert</option>
          </select>
          <!-- Dropdown arrow icon -->
          <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Search Form -->
      <div class="flex items-center ml-auto">
        <form method="GET" action="<?= url('admin/management_file') ?>" class="flex">
          <?php if (isset($_GET['converted']) && $_GET['converted'] === 'unconverted'): ?>
            <input type="hidden" name="converted" value="unconverted">
          <?php elseif (isset($_GET['show']) && $_GET['show'] === 'all'): ?>
            <input type="hidden" name="show" value="all">
          <?php else: ?>
            <?php if (isset($_GET['show'])): ?>
              <input type="hidden" name="show" value="<?= htmlspecialchars($_GET['show']) ?>">
            <?php elseif (isset($_GET['converted'])): ?>
              <input type="hidden" name="converted" value="<?= htmlspecialchars($_GET['converted']) ?>">
            <?php else: ?>
              <!-- Default: no filter parameter needed for all submissions -->
            <?php endif; ?>
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 0 014 0z"></path>
            </svg>
          </button>
          <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
            <a href="<?= url('admin/management_file') ?>?<?= isset($_GET['converted']) ? 'converted=' . htmlspecialchars($_GET['converted']) : (isset($_GET['show']) ? 'show=' . htmlspecialchars($_GET['show']) : '') ?>&page=1<?= isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '' ?><?= isset($_GET['order']) ? '&order=' . htmlspecialchars($_GET['order']) : '' ?>"
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
      <a href="<?= url('file/downloadAll') ?>" class="btn btn-primary text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium rounded-md transition duration-300 ease-in-out transform hover:scale-105 py-2 px-4">
        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        Unduh Semua Berkas (Terorganisir)
      </a>

    </div>
    
    <div class="card">
      <div class="overflow-x-auto overflow-y-visible">
        <table class="w-full divide-y divide-gray-200 table-auto">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-50 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('type', '<?= isset($_GET['sort']) && $_GET['sort'] === 'type' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['converted']) ? $_GET['converted'] : (isset($_GET['show']) ? $_GET['show'] : '') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Tipe
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'type'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('student_name', '<?= isset($_GET['sort']) && $_GET['sort'] === 'student_name' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['converted']) ? $_GET['converted'] : (isset($_GET['show']) ? $_GET['show'] : '') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Nama Mahasiswa
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'student_name'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('title', '<?= isset($_GET['sort']) && $_GET['sort'] === 'title' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['converted']) ? $_GET['converted'] : (isset($_GET['show']) ? $_GET['show'] : '') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Judul Skripsi
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'title'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-80">Berkas</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200" onclick="sortTable('date', '<?= isset($_GET['sort']) && $_GET['sort'] === 'date' && isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc' ?>', '<?= isset($_GET['converted']) ? $_GET['converted'] : (isset($_GET['show']) ? $_GET['show'] : '') ?>', '<?= isset($_GET['search']) ? urlencode($_GET['search']) : '' ?>')">
                Tanggal Pengajuan
                <?php if (isset($_GET['sort']) && $_GET['sort'] === 'date'): ?>
                  <span class="ml-1"><?= $_GET['order'] === 'asc' ? '↑' : '↓' ?></span>
                <?php endif; ?>
              </th>
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
                <tr class="hover:bg-gray-50">
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
                        <!-- Resubmission indicator removed as requested -->
                      <?php endif; ?>
                    </div>
                    <div class="text-sm text-gray-500"><?= htmlspecialchars($submission['nim']) ?></div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900 max-w-[250px] truncate" title="<?= htmlspecialchars($submission['judul_skripsi']) ?>"><?= htmlspecialchars($submission['judul_skripsi']) ?></div>
                  </td>
                  <td class="px-6 py-4">
                    <?php if (!empty($submission['files'])): ?>
                      <div class="flex flex-wrap gap-2 max-w-md">
                        <?php foreach ($submission['files'] as $file): ?>
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
                          <div class="flex flex-col items-center text-center min-w-[100px] max-w-[120px]">
                            <div class="text-xs font-medium text-gray-700 mb-1 truncate w-full" title="<?= htmlspecialchars($displayLabel) ?>"><?= htmlspecialchars($displayLabel) ?></div>
                            <a href="<?= url('file/view/' . $file['id']) ?>" target="_blank" class="btn btn-secondary btn-sm w-full text-center bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-md transition duration-300 ease-in-out transform hover:scale-105 py-1 px-2 text-xs">
                              <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                              </svg>
                              View
                            </a>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <span class="text-gray-400 text-sm">No files</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= format_datetime($submission['created_at']) ?>
                    <div class="text-xs text-gray-400"><?= format_time($submission['created_at']) ?></div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <!-- File upload form for converted files -->
                    <div class="mt-2 pt-2 border-t border-gray-200">
                      <form action="<?= url('file/uploadConvertedFile/' . $submission['id']) ?>" method="POST" enctype="multipart/form-data" class="flex flex-col gap-1" onsubmit="return confirm('Are you sure you want to upload this converted file? This will add the file to the existing submission.')">
                        <?= csrf_field() ?>
                        <input type="file" name="converted_file" accept=".pdf,.doc,.docx" class="text-xs mb-1" required>
                        <button type="submit" class="btn btn-success btn-sm text-xs py-1 px-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium rounded-md transition duration-300 ease-in-out transform hover:scale-105">Upload Converted</button>
                      </form>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a href="<?= url('file/download/' . $submission['id']) ?>" class="btn btn-secondary btn-sm bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium rounded-md transition duration-300 ease-in-out transform hover:scale-105 py-1 px-2 text-xs">
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
              <a href="<?= url('admin/management_file') ?>?page=<?= $currentPage - 1 ?><?= isset($_GET['converted']) && $_GET['converted'] === 'unconverted' ? '&converted=unconverted' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '' ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                Previous
              </a>
            <?php endif; ?>
            
            <?php
              // Show first page
              if ($currentPage > 3):
            ?>
              <a href="<?= url('admin/management_file') ?>?page=1<?= isset($_GET['converted']) && $_GET['converted'] === 'unconverted' ? '&converted=unconverted' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '' ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
              <a href="<?= url('admin/management_file') ?>?page=<?= $i ?><?= isset($_GET['converted']) && $_GET['converted'] === 'unconverted' ? '&converted=unconverted' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '' ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium <?= $i == $currentPage ? 'text-blue-600 border-blue-600' : 'text-gray-500 hover:bg-gray-50' ?>">
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
                <a href="<?= url('admin/management_file') ?>?page=<?= $totalPages ?><?= isset($_GET['converted']) && $_GET['converted'] === 'unconverted' ? '&converted=unconverted' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '' ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="px-3 py-2 border-t border-b border-gray-30 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                  <?= $totalPages ?>
                </a>
              <?php endif; ?>
            
            <?php if ($currentPage < $totalPages): ?>
              <a href="<?= url('admin/management_file') ?>?page=<?= $currentPage + 1 ?><?= isset($_GET['converted']) && $_GET['converted'] === 'unconverted' ? '&converted=unconverted' : (isset($showAll) && $showAll ? '&show=all' : '') ?><?= isset($search) && !empty($search) ? '&search=' . urlencode($search) : '' ?><?= isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '' ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
  
  .table-container {
    min-width: 100%;
  }
</style>

<script>
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
  url.searchParams.delete('converted');
  url.searchParams.delete('page'); // Reset to first page when changing filter
  url.searchParams.delete('search'); // Also clear search when changing filter
  
  // Set the appropriate parameter based on selection
  switch(selectedValue) {
    case 'all':
      url.searchParams.set('show', 'all');
      break;
    case 'unconverted':
      url.searchParams.set('converted', 'unconverted');
      break;
  }
  
  // Re-add sorting parameters if they existed
  if(sortParam) {
    url.searchParams.set('sort', sortParam);
  }
  if(orderParam) {
    url.searchParams.set('order', orderParam);
  }
  
  window.location.href = url.toString();
});

// Function to handle sorting
function sortTable(column, order, showType, search) {
  let url = new URL(window.location);
  
  // Set sorting parameters
  url.searchParams.set('sort', column);
  url.searchParams.set('order', order);
  
  // Preserve existing parameters
  if(showType) {
    if(showType === 'all') {
      url.searchParams.set('show', showType);
    } else if(showType === 'unconverted') {
      url.searchParams.set('converted', showType);
    }
  }
  
  if(search) {
    url.searchParams.set('search', search);
  }
  
  // Reset to first page when sorting
  url.searchParams.set('page', 1);
  
  window.location.href = url.toString();
}
</script>