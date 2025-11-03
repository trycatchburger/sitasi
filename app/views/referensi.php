<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold text-gray-900 mb-8">Daftar Referensi Saya</h1>

  <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-40 text-red-700 px-4 py-3 rounded mb-6">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if (isset($references) && !empty($references)): ?>
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($references as $reference): ?>
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                  <?php if ($reference['submission_type'] === 'journal'): ?>
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-full mr-3">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 0 00-2.5-2.5H15" />
                      </svg>
                    </div>
                  <?php else: ?>
                    <div class="w-10 h-10 flex items-center justify-center bg-green-10 rounded-full mr-3">
                      <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                  <?php endif; ?>
                  <h3 class="font-bold text-gray-900 line-clamp-2"><?= htmlspecialchars($reference['judul_skripsi']) ?></h3>
                </div>
                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                  <?= htmlspecialchars($reference['tahun_publikasi']) ?>
                </span>
              </div>

              <p class="text-gray-600 text-sm mb-3">
                <?= htmlspecialchars($reference['nama_mahasiswa']) ?>
              </p>

              <div class="text-xs text-gray-500 mb-4">
                <p><?= htmlspecialchars($reference['program_studi'] ?? 'N/A') ?></p>
              </div>

              <div class="flex flex-wrap gap-2 mb-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  <?= htmlspecialchars($reference['status']) ?>
                </span>
                <?php if ($reference['submission_type']): ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <?= htmlspecialchars(ucfirst($reference['submission_type'])) ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="flex space-x-3">
                <?php
                // Find the DOC file for this submission
                $docFile = null;
                if (isset($reference['files']) && is_array($reference['files'])) {
                    foreach ($reference['files'] as $file) {
                        $fileName = $file['file_name'];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        if ($fileExtension === 'doc' || $fileExtension === 'docx') {
                            $docFile = $file;
                            break;
                        }
                    }
                }
                ?>
                <div class="flex space-x-2">
                <?php if ($docFile): ?>
                <a href="<?= url('file/viewAsPdf/' . $docFile['id']) ?>"
                   target="_blank"
                   class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded-lg transition text-sm font-medium">
                  View
                </a>
                <?php endif; ?>
                <a href="<?= url('submission/' . ($reference['submission_type'] === 'journal' ? 'journalDetail' : 'detail') . '/' . $reference['id']) ?>"
                   class="flex-1 text-center bg-white border border-gray-30 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                  Detail
                </a>
                <button id="removeReferenceBtn_<?= $reference['id'] ?>"
                        class="flex-1 text-center bg-red-600 hover:bg-red-700 text-white py-2 px-3 rounded-lg transition text-sm font-medium"
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada referensi</h3>
      <p class="text-gray-600 mb-6">Anda belum menambahkan submission apapun ke referensi Anda.</p>
      <a href="<?= url('submission/repository') ?>" 
         class="inline-block bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition">
        Cari Submission
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Remove reference confirmation modal -->
<div id="removeReferenceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
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
      const csrftoken = document.querySelector('input[name="csrftoken"]').value;
      
      const response = await fetch('<?= url('submission/toggleReference') ?>', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrftoken
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
          
          // Show success message
          const messageDiv = document.createElement('div');
          messageDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6';
          messageDiv.textContent = 'Submission berhasil dihapus dari referensi';
          document.querySelector('.container').insertBefore(messageDiv, document.querySelector('.container').firstChild);
          
          // Auto-remove message after 3 seconds
          setTimeout(() => {
            messageDiv.remove();
          }, 3000);
          
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
</script>

<?php
$title = 'Referensi Saya | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>