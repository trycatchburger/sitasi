<?php ob_start(); ?>

<div class="px-4 py-4">
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-800">Manajemen Repository</h1>
    <p class="text-gray-600 mt-1">Halaman ini menampilkan skripsi, tesis, dan jurnal yang dapat diakses oleh publik melalui repository</p>
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
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mahasiswa</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berkas</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Repository</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (empty($submissions)): ?>
            <tr>
              <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                No approved submissions found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($submissions as $submission): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">
                    <?= htmlspecialchars($submission['nama_mahasiswa']) ?>
                  </div>
                  <?php
                  // Display additional authors if they exist (for journal submissions)
                  $additional_authors = [];
                  if (!empty($submission['author_2'])) $additional_authors[] = htmlspecialchars($submission['author_2']);
                  if (!empty($submission['author_3'])) $additional_authors[] = htmlspecialchars($submission['author_3']);
                  if (!empty($submission['author_4'])) $additional_authors[] = htmlspecialchars($submission['author_4']);
                  if (!empty($submission['author_5'])) $additional_authors[] = htmlspecialchars($submission['author_5']);
                  
                  if (!empty($additional_authors)): ?>
                  <div class="text-sm text-gray-600 mt-1">
                      <span class="font-medium">Additional Authors:</span>
                      <span class="ml-1"><?= implode(', ', $additional_authors) ?></span>
                  </div>
                  <?php endif; ?>
                  <div class="text-sm text-gray-500"><?= htmlspecialchars($submission['nim'] ?? '') ?></div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($submission['judul_skripsi']) ?></div>
                  <?php if ($submission['submission_type'] === 'journal' && !empty($submission['abstract'])): ?>
                  <div class="text-xs text-gray-500 mt-1 truncate" title="<?= htmlspecialchars($submission['abstract']) ?>">Abstract: <?= htmlspecialchars(substr($submission['abstract'], 0, 100)) . (strlen($submission['abstract']) > 10 ? '...' : '') ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php
                    $submissionType = $submission['submission_type'] ?? 'bachelor';
                    $typeLabel = 'Skripsi';
                    $typeColor = 'bg-blue-10 text-blue-800';
                    
                    if ($submissionType === 'master') {
                        $typeLabel = 'Tesis';
                        $typeColor = 'bg-purple-100 text-purple-800';
                    } elseif ($submissionType === 'journal') {
                        $typeLabel = 'Jurnal';
                        $typeColor = 'bg-green-100 text-green-800';
                    }
                  ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $typeColor ?>">
                    <?= $typeLabel ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <?php if (!empty($submission['files'])): ?>
                    <div class="flex flex-wrap gap-1">
                      <?php foreach ($submission['files'] as $file): ?>
                        <a href="<?= url('file/view/' . $file['id']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2V5a2 2 0 012-2h5.586a1 1 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 01-2 2z"></path>
                          </svg>
                          Lihat
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-400 text-sm">No files</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php
                    $status = htmlspecialchars($submission['status']);
                    $badge_color = 'bg-gray-10 text-gray-800'; // Default for Pending
                    if ($status === 'Diterima') {
                      $badge_color = 'bg-green-100 text-green-800';
                    } elseif ($status === 'Ditolak') {
                      $badge_color = 'bg-red-100 text-red-800';
                    } elseif ($status === 'Digantikan') {
                      $badge_color = 'bg-yellow-10 text-yellow-800';
                    }
                  ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge_color ?>">
                    <?= $status ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php if ($submission['status'] === 'Diterima'): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Published
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                      Unpublished
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?= format_datetime($submission['created_at']) ?>
                  <div class="text-xs text-gray-400"><?= format_time($submission['created_at']) ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex flex-col gap-2">
                    <a href="<?= url('submission/' . (!empty($submission['author_2']) ? 'journalDetail' : 'detail') . '/' . $submission['id']) ?>" class="btn btn-secondary btn-sm">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.47 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      Lihat
                    </a>
                    <?php if ($submission['status'] === 'Diterima'): ?>
                      <!-- Unpublish form for published submissions -->
                      <form action="<?= url('admin/unpublishFromRepository') ?>" method="POST" class="mt-1 unpublish-form">
                        
                        <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                          <button type="submit" class="btn btn-danger btn-sm text-white flex items-center" onclick="return confirm('Are you sure you want to unpublish this submission from the repository?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                              <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Tarik dari Publikasi
                          </button>

                      </form>
                    <?php else: ?>
                      <!-- Republish form for unpublished submissions -->
                      <form action="<?= url('admin/republishToRepository') ?>" method="POST" class="mt-1 republish-form">
                        
                        <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                         <button type="submit" class="btn btn-primary btn-sm text-white flex items-center" onclick="return confirm('Are you sure you want to republish this submission to the repository?')" aria-label="Republish submission">
                           <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                             <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                           </svg>Publikasikan
                         </button>

                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle unpublish form submissions via AJAX
    const unpublishForms = document.querySelectorAll('form.unpublish-form');
    
    unpublishForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get submission ID and row element
            const submissionId = form.querySelector('input[name="submission_id"]').value;
            const row = form.closest('tr');
            
            // Show confirmation dialog
            if (!confirm('Are you sure you want to unpublish this submission from the repository?')) {
                return;
            }
            
            // Check if this is a journal submission by looking for additional authors in the row
            const isJournal = row.querySelector('div.text-sm.text-gray-600.mt-1 span.font-medium') !== null;
            const detailRoute = isJournal ? 'journalDetail' : 'detail';
            
            // Create FormData object
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch('<?= url('admin/unpublishFromRepository') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showMessage(data.message, 'success');
                    
                    // Update the repository status column to show "Unpublished"
                    const statusCell = row.querySelector('td:nth-child(6)'); // Repository Status column (shifted by 1 due to new Tipe column)
                    if (statusCell) {
                        statusCell.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Unpublished</span>';
                    }
                    
                    // Replace the unpublish form with republish form
                    const actionCell = row.querySelector('td:nth-child(8)'); // Actions column (shifted by 1 due to new Tipe column)
                    if (actionCell) {
                        actionCell.innerHTML = `
                          <div class="flex flex-col gap-2">
                            <a href="<?= url('submission/') ?>${detailRoute}/${submissionId}" class="btn btn-secondary btn-sm">
                              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                              </svg>
                              View
                            </a>
                            <form action="<?= url('admin/republishToRepository') ?>" method="POST" class="mt-1 republish-form">
                              
                              <input type="hidden" name="submission_id" value="${submissionId}">
                              <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Are you sure you want to republish this submission to the repository?')">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Republish
                              </button>
                            </form>
                          </div>
                        `;
                        
                        // Add event listener to the new republish form
                        const newRepublishForm = actionCell.querySelector('form.republish-form');
                        if (newRepublishForm) {
                            newRepublishForm.addEventListener('submit', handleRepublish);
                        }
                    }
                } else {
                    // Show error message
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while processing your request.', 'error');
            });
        });
    });
    
    // Handle republish form submissions via AJAX
    const republishForms = document.querySelectorAll('form.republish-form');
    
    republishForms.forEach(function(form) {
        form.addEventListener('submit', handleRepublish);
    });
    
    function handleRepublish(e) {
        e.preventDefault();
        
        // Get form element
        const form = e.target.closest('form');
        
        // Get submission ID and row element
        const submissionId = form.querySelector('input[name="submission_id"]').value;
        const row = form.closest('tr');
        
        // Show confirmation dialog
        if (!confirm('Are you sure you want to republish this submission to the repository?')) {
            return;
        }
        
        // Create FormData object
        const formData = new FormData(form);
        
        // Send AJAX request
        fetch('<?= url('admin/republishToRepository') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showMessage(data.message, 'success');
                
                // Update the repository status column to show "Published"
                const statusCell = row.querySelector('td:nth-child(6)'); // Repository Status column (shifted by 1 due to new Tipe column)
                if (statusCell) {
                    statusCell.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>';
                }
                
                // Replace the republish form with unpublish form
                const actionCell = row.querySelector('td:nth-child(8)'); // Actions column (shifted by 1 due to new Tipe column)
                if (actionCell) {
                    actionCell.innerHTML = `
                      <div class="flex flex-col gap-2">
                        <a href="<?= url('submission/') ?>${detailRoute}/${submissionId}" class="btn btn-secondary btn-sm">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                          </svg>
                          View
                        </a>
                        <form action="<?= url('admin/unpublishFromRepository') ?>" method="POST" class="mt-1 unpublish-form">
                          
                          <input type="hidden" name="submission_id" value="${submissionId}">
                          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to unpublish this submission from the repository?')">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Unpublish
                          </button>
                        </form>
                      </div>
                    `;
                    
                    // Add event listener to the new unpublish form
                    const newUnpublishForm = actionCell.querySelector('form.unpublish-form');
                    if (newUnpublishForm) {
                        newUnpublishForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            // Find the parent unpublishForms collection and trigger the handler
                            unpublishForms.forEach(function(unpublishForm) {
                                if (unpublishForm === newUnpublishForm) {
                                    // Manually trigger the event
                                    const event = new Event('submit', { cancelable: true });
                                    newUnpublishForm.dispatchEvent(event);
                                }
                            });
                        });
                    }
                }
            } else {
                // Show error message
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while processing your request.', 'error');
        });
    }
    
    // Function to show messages
    function showMessage(message, type) {
        // Create message container if it doesn't exist
        let messageContainer = document.getElementById('ajax-message-container');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'ajax-message-container';
            messageContainer.className = 'fixed top-4 right-4 z-50';
            document.body.appendChild(messageContainer);
        }
        
        // Create message element
        const messageElement = document.createElement('div');
        messageElement.className = `mb-4 p-4 rounded-lg shadow-lg max-w-sm ${
            type === 'success'
                ? 'bg-green-100 border border-green-200 text-green-800'
                : 'bg-red-100 border border-red-200 text-red-800'
        }`;
        messageElement.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${type === 'success'
                        ? '<svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 00 16zm3.707-9.293a1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>'
                        : '<svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 0-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>'
                    }
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex bg-${type === 'success' ? 'green' : 'red'}-100 rounded-md p-1.5 text-${type === 'success' ? 'green' : 'red'}-500 hover:bg-${type === 'success' ? 'green' : 'red'}-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-${type === 'success' ? 'green' : 'red'}-50 focus:ring-${type === 'success' ? 'green' : 'red'}-600">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add close functionality
        const closeButton = messageElement.querySelector('button');
        closeButton.addEventListener('click', function() {
            messageElement.remove();
        });
        
        // Add to container
        messageContainer.appendChild(messageElement);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            if (messageElement.parentNode) {
                messageElement.remove();
            }
        }, 5000);
    }
});
</script>

<?php
$title = 'Manajemen Repository | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>