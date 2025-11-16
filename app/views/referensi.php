<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
    <h1 class="text-3xl font-bold text-gray-900">Daftar Referensi Saya</h1>
    <div class="flex items-center space-x-3">
      <div class="flex border rounded-lg overflow-hidden">
        <button id="gridViewBtn" class="px-3 py-1.5 text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition">Grid</button>
        <button id="listViewBtn" class="px-3 py-1.5 text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300 transition">List</button>
      </div>
    </div>
  </div>

  <?php if (isset($error)): ?>
    <div class="bg-red-100 border-red-40 text-red-70 px-4 py-3 rounded mb-6">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if (isset($references) && !empty($references)): ?>
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($references as $reference): ?>
            <div class="reference-item border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow bg-white">
              <div class="flex flex-col sm:flex-row justify-between items-start sm:items-start gap-4 mb-4">
                <div class="flex items-start flex-1 min-w-0">
                  <?php if ($reference['submission_type'] === 'journal'): ?>
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-full mr-3 flex-shrink-0 mt-0.5">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 0 00-2.5H15" />
                      </svg>
                    </div>
                  <?php else: ?>
                    <div class="w-10 h-10 flex items-center justify-center bg-green-10 rounded-full mr-3 flex-shrink-0 mt-0.5">
                      <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                  <?php endif; ?>
                  <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 line-clamp-2"><?= htmlspecialchars($reference['judul_skripsi']) ?></h3>
                    <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($reference['nama_mahasiswa']) ?></p>
                    <div class="text-xs text-gray-500 mt-1">
                      <p><?= htmlspecialchars($reference['program_studi'] ?? 'N/A') ?></p>
                    </div>
                  </div>
                </div>
                <div class="flex flex-shrink-0 flex-col items-end gap-2 min-w-[120px]">
                  <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded self-start">
                    <?= htmlspecialchars($reference['tahun_publikasi']) ?>
                  </span>
                  <div class="flex flex-wrap gap-1 justify-end">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                      <?= htmlspecialchars($reference['status']) ?>
                    </span>
                    <?php if ($reference['submission_type']): ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                        <?= htmlspecialchars(ucfirst($reference['submission_type'])) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2 sm:gap-0 mt-4 pt-4 border-t border-gray-100">
                <?php
                // Find the DOC/PDF file for this submission (prioritizing the converted PDF from DOC files)
                $docFile = null;
                $pdfFile = null;
                $convertedPdfFile = null;
                
                if (isset($reference['files']) && is_array($reference['files'])) {
                    foreach ($reference['files'] as $file) {
                        $fileName = $file['file_name'];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        if ($fileExtension === 'doc' || $fileExtension === 'docx') {
                            $docFile = $file;
                        } else if ($fileExtension === 'pdf') {
                            // Check if this PDF is likely a converted version of a DOC file
                            // Look for PDFs that have similar names to DOC files, especially those with converted indicators
                            $isConvertedPdf = false;
                            
                            if ($docFile) {
                                $docBaseName = pathinfo($docFile['file_name'], PATHINFO_FILENAME);
                                $pdfBaseName = pathinfo($fileName, PATHINFO_FILENAME);
                                
                                // Check if this PDF has the same base name as the DOC file or contains conversion indicators
                                if (stripos($fileName, $docBaseName) !== false &&
                                    (stripos($pdfBaseName, 'converted') !== false || stripos($pdfBaseName, '_pdf') !== false ||
                                    preg_replace('/\.(doc|docx)$/i', '', $docFile['file_name']) === $pdfBaseName)) {
                                    $convertedPdfFile = $file;
                                    $isConvertedPdf = true;
                                }
                            }
                            
                            // If we haven't found a converted PDF yet and this isn't a converted one, store as fallback
                            if (!$pdfFile && !$isConvertedPdf) {
                                $pdfFile = $file;
                            }
                        }
                    }
                    
                    // If no converted PDF was found but we have a DOC file, try to find a PDF with matching base name
                    if (!$convertedPdfFile && $docFile) {
                        $docBaseName = pathinfo($docFile['file_name'], PATHINFO_FILENAME);
                        foreach ($reference['files'] as $file) {
                            $fileName = $file['file_name'];
                            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            
                            if ($fileExtension === 'pdf') {
                                $pdfBaseName = pathinfo($fileName, PATHINFO_FILENAME);
                                // Check if the PDF has the same base name as the DOC file (which would be the converted version)
                                if (stripos($fileName, $docBaseName) !== false && $docBaseName !== $pdfBaseName) {
                                    $convertedPdfFile = $file;
                                    break;
                                } elseif ($pdfBaseName === $docBaseName) {
                                    // Direct conversion (same base name)
                                    $convertedPdfFile = $file;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Prioritize the converted PDF file if found, otherwise use any PDF file
                $fileToShow = $convertedPdfFile ?: $pdfFile;
                ?>
                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2 sm:gap-0 flex-1 min-w-0">
                <?php if ($docFile || $fileToShow): ?>
                <?php if ($fileToShow): ?>
                <a href="<?= url('file/cleanPdfJsView/' . $fileToShow['id']) ?>"
                   target="_blank"
                   class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded-lg transition text-sm font-medium">
                  View PDF
                </a>
                <?php elseif ($docFile): ?>
                <a href="<?= url('file/cleanPdfJsView/' . $docFile['id']) ?>"
                   target="_blank"
                   class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded-lg transition text-sm font-medium">
                  View
                </a>
                <?php endif; ?>
                <?php endif; ?>
                <a href="<?= url('submission/' . ($reference['submission_type'] === 'journal' ? 'journalDetail' : 'detail') . '/' . $reference['id']) ?>"
                   class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg transition text-sm font-medium whitespace-nowrap">
                  Detail
                </a>
                <button id="removeReferenceBtn_<?= $reference['id'] ?>"
                        class="flex-1 text-center bg-red-600 hover:bg-red-700 text-white py-2 px-3 rounded-lg transition text-sm font-medium whitespace-nowrap"
                        data-submission-id="<?= $reference['id'] ?>">
                  Hapus
                </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
      <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada referensi</h3>
      <p class="text-gray-600 mb-6">Anda belum menambahkan submission apapun ke referensi Anda.</p>
      <a href="<?= url('submission/repository') ?>" 
         class="inline-block bg-green-600 hover:bg-green-70 text-white font-medium py-3 px-6 rounded-lg transition">
        Cari Submission
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Remove reference confirmation modal -->
<div id="removeReferenceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Referensi</h3>
    <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus submission ini dari referensi Anda?</p>
    <div class="flex space-x-3">
      <button id="cancelRemoveBtn" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg transition">
        Batal
      </button>
      <button id="confirmRemoveBtn" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition">
        Hapus
      </button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Grid/List view toggle functionality
  const gridViewBtn = document.getElementById('gridViewBtn');
  const listViewBtn = document.getElementById('listViewBtn');
  const referencesContainer = document.querySelector('.grid.grid-cols-1');
  const referenceItems = document.querySelectorAll('.reference-item');
  
  if (gridViewBtn && listViewBtn && referencesContainer) {
    // Set initial view as grid
    gridViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
    gridViewBtn.classList.add('bg-green-600', 'text-white');
    
    listViewBtn.classList.remove('bg-green-600', 'text-white');
    listViewBtn.classList.add('bg-gray-200', 'text-gray-700');
    
    // Add click event for grid view
    gridViewBtn.addEventListener('click', function() {
      // Remove list view classes
      referencesContainer.classList.remove('space-y-4');
      // Add grid view classes
      referencesContainer.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-6');
      
      // Apply grid styles to reference items
      referenceItems.forEach(item => {
        item.classList.remove('flex', 'flex-col', 'md:flex-row', 'items-start', 'p-4', 'border', 'border-gray-200', 'bg-white');
        item.classList.add('rounded-xl', 'p-6', 'border', 'border-gray-200');
      });
      
      // Update button states
      gridViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
      gridViewBtn.classList.add('bg-green-600', 'text-white');
      listViewBtn.classList.remove('bg-green-600', 'text-white');
      listViewBtn.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    // Add click event for list view
    listViewBtn.addEventListener('click', function() {
      // Remove grid view classes
      referencesContainer.classList.remove('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-6');
      // Add list view classes
      referencesContainer.classList.add('space-y-4');
      
      // Apply list styles to reference items
      referenceItems.forEach(item => {
        item.classList.remove('rounded-xl', 'p-6');
        item.classList.add('flex', 'flex-col', 'md:flex-row', 'items-start', 'p-4', 'border', 'border-gray-200', 'bg-white');
      });
      
      // Update button states
      listViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
      listViewBtn.classList.add('bg-green-600', 'text-white');
      gridViewBtn.classList.remove('bg-green-600', 'text-white');
      gridViewBtn.classList.add('bg-gray-200', 'text-gray-700');
    });
  }

  // Remove reference functionality
  const removeButtons = document.querySelectorAll('[id^="removeReferenceBtn_"]');
  const modal = document.getElementById('removeReferenceModal');
  const cancelRemoveBtn = document.getElementById('cancelRemoveBtn');
  const confirmRemoveBtn = document.getElementById('confirmRemoveBtn');
  let submissionIdToRemove = null;
  
  removeButtons.forEach(button => {
    button.addEventListener('click', function() {
      submissionIdToRemove = this.getAttribute('data-submission-id');
      modal.classList.remove('hidden');
    });
  });
  
  cancelRemoveBtn.addEventListener('click', function() {
    modal.classList.add('hidden');
    submissionIdToRemove = null;
 });
  
  confirmRemoveBtn.addEventListener('click', async function() {
    if (!submissionIdToRemove) return;
    
    try {
      const csrftoken = document.getElementById('csrf_token').value;
      
      const response = await fetch('<?= url('submission/toggleReference') ?>', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrftoken,
          'X-CSRFToken': csrftoken  // Also try the alternate header name
        },
        body: JSON.stringify({
          submission_id: parseInt(submissionIdToRemove)
        })
      });
      
      if (response.ok) {
        const result = await response.json();
        if (result.success) {
          // Remove the reference from the UI
          const referenceElement = document.querySelector(`[data-submission-id="${submissionIdToRemove}"]`).closest('.border');
          if (referenceElement) {
            referenceElement.remove();
          }
          
          // Create and show success popup
          createSuccessPopup('Submission berhasil dihapus dari referensi');
          
          // Hide modal and reset
          modal.classList.add('hidden');
          submissionIdToRemove = null;
        } else {
          throw new Error(result.message || 'Terjadi kesalahan');
        }
      } else {
        const error = await response.json();
        throw new Error(error.message || 'Terjadi kesalahan server');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Gagal menghapus referensi: ' + error.message);
      modal.classList.add('hidden');
      submissionIdToRemove = null;
    }
  });
});

// Function to create success popup
function createSuccessPopup(message) {
  // Remove any existing popup
  const existingPopup = document.getElementById('reference-success-popup');
  if (existingPopup) {
    existingPopup.remove();
 }
  
  // Create popup container
  const popup = document.createElement('div');
  popup.id = 'reference-success-popup';
  popup.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50 max-w-sm';
  popup.innerHTML = `
    <div class="flex items-start">
      <svg class="w-5 h-5 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      <div class="flex-1">
        <div class="font-medium">Sukses!</div>
        <div class="text-sm">${message}</div>
      </div>
      <button class="ml-2 text-green-70 hover:text-green-900" onclick="this.parentElement.parentElement.remove()">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
  `;
  
  // Add to page
 document.body.appendChild(popup);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (popup.parentNode) {
      popup.remove();
    }
 }, 5000);
}
</script>

<?php
$title = 'Referensi Saya | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>