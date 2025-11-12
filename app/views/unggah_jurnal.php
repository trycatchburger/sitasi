<?php ob_start(); ?>

<div class="w-full bg-green-50 min-h-screen">
    <div class="w-full px-4">

        <div class="text-center mb-8 bg-white rounded-xl shadow-md p-8 border-b-4 border-green-600">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">UNGGAH JURNAL</h1>
            <h2 class="text-xl font-semibold text-gray-600">STAIN SULTAN ABDURRAHMAN KEPULAUAN RIAU</h2>
        </div>

        <div class="w-full flex flex-col md:flex-row gap-6">
            <section class="md:w-3/4 bg-white rounded-xl shadow-md p-10">
                <div class="max-w-5xl mx-auto bg-white">
                    <div class="card">
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error mb-6">
                                <svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 1-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="alert-content">
                                    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($errors) && is_array($errors)): ?>
                            <div class="alert alert-error mb-6">
                                <svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 1-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="alert-content">
                                    <strong>Validation Errors:</strong>
                                    <ul class="list-disc pl-5 mt-2">
                                        <?php foreach ($errors as $field => $fieldErrors): ?>
                                            <?php foreach ($fieldErrors as $fieldError): ?>
                                                <li><?= htmlspecialchars($fieldError) ?></li>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($is_resubmission) && $is_resubmission): ?>
                        <form action="<?php echo url('submission/resubmit'); ?>" method="post" enctype="multipart/form-data" id="journal-form">
                            <input type="hidden" name="submission_id" value="<?= htmlspecialchars($submission_id) ?>">
                            <input type="hidden" name="submission_type" value="journal">
                        <?php else: ?>
                        <form action="<?php echo url('submission/create_journal'); ?>" method="post" enctype="multipart/form-data" id="journal-form">
                        <?php endif; ?>
                            <!-- Personal Information Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informasi Penulis</h2>
                                
                                <div class="form-group">
                                    <label for="nama_penulis" class="form-label">Nama Penulis Utama</label>
                                    <input type="text" id="nama_penulis" name="nama_penulis" required
                                        class="form-control"
                                        value="<?= isset($old_data['nama_penulis']) ? htmlspecialchars($old_data['nama_penulis']) : (isset($user_details['name']) ? htmlspecialchars($user_details['name']) : '') ?>">
                                    <p class="text-xs text-gray-50 mt-1">Contoh: Iis Rahayu. Gunakan huruf kapital di awal kata.</p>
                                    
                                    <?php if (isset($errors['nama_penulis'])): ?>
                                        <div class="text-red-500 text-sm mt-1">
                                            <?php foreach ($errors['nama_penulis'] as $error): ?>
                                                <div><?= htmlspecialchars($error) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group mt-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" required class="form-control"
                                           value="<?= isset($old_data['email']) ? htmlspecialchars($old_data['email']) : (isset($user_details['email']) ? htmlspecialchars($user_details['email']) : '') ?>">
                                    <p class="text-xs text-gray-500 mt-1">Contoh: nama@domain.com</p>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="text-red-500 text-sm mt-1">
                                            <?php foreach ($errors['email'] as $error): ?>
                                                <div><?= htmlspecialchars($error) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Additional Authors Section -->
                                <div class="mt-8">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-700">Penulis Tambahan (Opsional)</h3>
                                        <button type="button" id="add-author-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Tambah Penulis
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-4">Jika jurnal memiliki lebih dari satu penulis, tambahkan nama-nama penulis tambahan di bawah ini:</p>
                                    
                                    <div id="authors-container" class="space-y-4">
                                        <!-- Dynamic author fields will be added here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Journal Information Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informasi Jurnal</h2>
                                
                                <div class="form-group">
                                    <label for="judul_jurnal" class="form-label">Judul Jurnal</label>
                                    <input type="text" id="judul_jurnal" name="judul_jurnal" required class="form-control"
                                           value="<?= isset($old_data['judul_jurnal']) ? htmlspecialchars($old_data['judul_jurnal']) : '' ?>">
                                    <p class="text-xs text-gray-500 mt-1">Contoh: Analisis Kinerja Sistem Informasi Manajemen. Gunakan huruf kapital di awal kata.</p>
                                    <?php if (isset($errors['judul_jurnal'])): ?>
                                        <div class="text-red-500 text-sm mt-1">
                                            <?php foreach ($errors['judul_jurnal'] as $error): ?>
                                                <div><?= htmlspecialchars($error) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col md:col-6">
                                        <div class="form-group">
                                            <label for="tahun_publikasi" class="form-label">Tahun Publikasi</label>
                                            <select id="tahun_publikasi" name="tahun_publikasi" required
                                                    class="form-control form-select">
                                                <option value="">Pilih Tahun</option>
                                                <?php
                                                $currentYear = date('Y');
                                                for ($year = $currentYear; $year >= $currentYear - 5; $year--): ?>
                                                    <option value="<?= $year ?>"
                                                        <?= (isset($old_data['tahun_publikasi']) && $old_data['tahun_publikasi'] == $year) ? 'selected' : '' ?>>
                                                        <?= $year ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                            <?php if (isset($errors['tahun_publikasi'])): ?>
                                                <div class="text-red-500 text-sm mt-1">
                                                    <?php foreach ($errors['tahun_publikasi'] as $error): ?>
                                                        <div><?= htmlspecialchars($error) ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="abstrak" class="form-label">Abstrak</label>
                                    <textarea id="abstrak" name="abstrak" required rows="6" class="form-control"
                                              placeholder="Tuliskan abstrak jurnal Anda di sini..."><?= isset($old_data['abstrak']) ? htmlspecialchars($old_data['abstrak']) : '' ?></textarea>
                                    <?php if (isset($errors['abstrak'])): ?>
                                        <div class="text-red-500 text-sm mt-1">
                                            <?php foreach ($errors['abstrak'] as $error): ?>
                                                <div><?= htmlspecialchars($error) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Document Upload Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Unggah File</h2>
                                
                                <div class="space-y-6">
                                    <div class="form-group">
                                        <label for="cover_jurnal" class="form-label">Cover Jurnal (.jpg, .jpeg)</label>
                                        <input type="file" id="cover_jurnal" name="cover_jurnal" required
                                            class="form-control" accept=".jpg,.jpeg">
                                        <div class="form-text">(Format Nama File: COVER_NAMA_PENULIS)</div>
                                        <?php if (isset($errors['cover_jurnal'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['cover_jurnal'] as $error): ?>
                                                    <div><?= htmlspecialchars($error) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="file_jurnal" class="form-label">File Jurnal (.pdf)</label>
                                        <input type="file" id="file_jurnal" name="file_jurnal" required
                                            class="form-control" accept=".pdf">
                                        <div class="form-text">(Format Nama File: JUDUL_JURNAL. Ukuran maksimum file: 10240KB.)</div>
                                        <?php if (isset($errors['file_jurnal'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['file_jurnal'] as $error): ?>
                                                    <div><?= htmlspecialchars($error) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6">
                                <a href="<?php echo url(); ?>" class="btn btn-ghost flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Back to Home
                                </a>
                                <button type="submit" class="bg-green-900 hover:bg-green-600 text-white text-lg font-semibold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section id="info-section" class="w-full lg:w-1/3 bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md p-6 space-y-6 border border-green-200 opacity-0 translate-y-8 transition-all duration-700 ease-out">
                <h3 class="text-2xl font-bold text-green-800 mb-4 flex items-center gap-2"> Informasi Penting
                </h3>

                <!-- Batas Waktu -->
                <div class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition">
                <h4 class="font-semibold text-green-800 text-lg flex items-center gap-2 mb-2">
                    ⏰ Batas Waktu Unggah
                </h4>
                <p class="text-gray-700 text-sm">Unggah jurnal paling lambat <strong>31 Desember 2025</strong>.</p>
                </div>

                <!-- Alur Unggah -->
                <div class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition">
                <h4 class="font-semibold text-green-80 text-lg flex items-center gap-2 mb-2">
                    📄 Alur Unggah
                </h4>
                <ol class="list-decimal pl-5 text-gray-700 text-sm space-y-1">
                    <li>Isi data dan unggah berkas jurnal.</li>
                    <li>Pastikan form terisi dengan benar.</li>
                    <li>Tunggu verifikasi dari admin perpustakaan.</li>
                    <li>Cek email untuk menerima bukti diterima.</li>
                </ol>
                </div>

                <!-- Bantuan Teknis -->
                <div class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition">
                    <h4 class="font-semibold text-green-800 text-lg flex items-center gap-2 mb-2">
                        💬 Bantuan Teknis
                    </h4>
                    <ul class="list-disc pl-5 text-gray-70 text-sm space-y-1">
                        <li>WhatsApp: <a href="https://wa.me/6283184662330" class="text-green-700 font-medium hover:underline">+62 831-8466-2339</a></li>
                        <li>Email: <a href="mailto:perpustakaan@stainkepri.ac.id" class="text-green-700 font-medium hover:underline">repository@stainkepri.ac.id</a></li>
                        <li>Panduan unggah: <a href="https://drive.google.com/..." target="_blank" class="text-green-700 font-medium hover:underline">Klik di sini</a></li>
                        <li>
                            Tutorial video: 
                            <div class="mt-2">
                                <iframe width="280" height="157" src="https://www.youtube.com/embed/82x-k2gXsKg" title="Tutorial Upload" frameborder="0" allowfullscreen class="rounded-lg shadow-md"></iframe>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Format Dokumen -->
                <div class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition">
                    <h4 class="font-semibold text-green-800 text-lg flex items-center gap-2 mb-3">
                        📘 Format Dokumen
                    </h4>

                <div class="text-gray-700 text-sm space-y-4">

                    <!-- Cover File -->
                    <div class="border border-green-200 rounded-md p-3 bg-green-50/50">
                        <p><strong>• Cover Jurnal – Format:</strong> JPG/JPEG</p>
                        <p class="ml-4">Nama file: <code class="bg-gray-100 px-1 rounded text-green-800">COVER_NAMA_PENULIS</code></p>
                        <p class="ml-4 font-semibold mt-1">Isi:</p>
                        <ul class="list-disc pl-8">
                            <li>Cover jurnal yang menarik dan informatif</li>
                        </ul>
                    </div>

                    <!-- Journal File -->
                    <div class="border border-green-200 rounded-md p-3 bg-green-50/50">
                        <p><strong>• File Jurnal – Format:</strong> PDF</p>
                        <p class="ml-4">Nama file: <code class="bg-gray-100 px-1 rounded text-green-800">JUDUL JURNAL</code></p>
                        <p class="ml-4 font-semibold mt-1">Isi:</p>
                        <ul class="list-disc pl-8">
                            <li>File jurnal lengkap dalam format PDF</li>
                        </ul>
                    </div>

                </div>


    <!-- Verifikasi -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition">
      <h4 class="font-semibold text-green-800 text-lg flex items-center gap-2 mb-2">
        📩 Verifikasi
      </h4>
      <p class="text-gray-700 text-sm">
        Cek email secara berkala untuk menerima hasil verifikasi unggahan Anda.
      </p>
    </div>
                                            

  </section>
</div>
            </div>

<style>
.author-field {
  position: relative;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  background-color: #f9fafb;
  transition: all 0.3s ease;
}

.author-field:hover {
  border-color: #d1d5db;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.author-field .form-label {
  margin-bottom: 0.5rem;
}

.author-field .form-control {
  margin-bottom: 0;
}
</style>

<!-- Script animasi scroll -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const section = document.querySelector('#info-section');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        section.classList.remove('opacity-0', 'translate-y-8');
        section.classList.add('opacity-100', 'translate-y-0');
      }
    });
  });
  observer.observe(section);

  // Dynamic author fields functionality
  let authorCount = 0;
  
  // Add author button functionality
  document.getElementById('add-author-btn').addEventListener('click', function() {
    authorCount++;
    const authorsContainer = document.getElementById('authors-container');
    
    // Create author field container
    const authorDiv = document.createElement('div');
    authorDiv.className = 'form-group author-field';
    authorDiv.id = `author-field-${authorCount}`;
    
    // Get the current value if we're re-populating from old data
    const currentValue = authorCount === 1 ? `<?= isset($old_data['author_2']) ? addslashes(htmlspecialchars($old_data['author_2'])) : '' ?>` :
                        authorCount === 2 ? `<?= isset($old_data['author_3']) ? addslashes(htmlspecialchars($old_data['author_3'])) : '' ?>` :
                        authorCount === 3 ? `<?= isset($old_data['author_4']) ? addslashes(htmlspecialchars($old_data['author_4'])) : '' ?>` :
                        authorCount === 4 ? `<?= isset($old_data['author_5']) ? addslashes(htmlspecialchars($old_data['author_5'])) : '' ?>` : '';
    
    authorDiv.innerHTML = `
      <div class="flex justify-between items-center mb-2">
        <label for="author_${authorCount + 1}" class="form-label">Nama Penulis Ke-${authorCount + 1}</label>
        <button type="button" class="remove-author-btn text-red-600 hover:text-red-800" data-id="${authorCount}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <input type="text" id="author_${authorCount + 1}" name="author_${authorCount + 1}" class="form-control"
             value="${currentValue}" placeholder="Contoh: John Doe">
    `;
    
    authorsContainer.appendChild(authorDiv);
    
    // Add event listener to the remove button
    authorDiv.querySelector('.remove-author-btn').addEventListener('click', function() {
      const fieldId = this.getAttribute('data-id');
      document.getElementById(`author-field-${fieldId}`).remove();
    });
  });

  // Pre-populate with existing author fields if they exist in old_data
  const existingAuthors = [
    '<?= isset($old_data['author_2']) ? addslashes(htmlspecialchars($old_data['author_2'])) : '' ?>',
    '<?= isset($old_data['author_3']) ? addslashes(htmlspecialchars($old_data['author_3'])) : '' ?>',
    '<?= isset($old_data['author_4']) ? addslashes(htmlspecialchars($old_data['author_4'])) : '' ?>',
    '<?= isset($old_data['author_5']) ? addslashes(htmlspecialchars($old_data['author_5'])) : '' ?>'
  ];
  
  existingAuthors.forEach((author, index) => {
    if (author) {
      authorCount++;
      const authorsContainer = document.getElementById('authors-container');
      
      const authorDiv = document.createElement('div');
      authorDiv.className = 'form-group author-field';
      authorDiv.id = `author-field-${authorCount}`;
      
      authorDiv.innerHTML = `
        <div class="flex justify-between items-center mb-2">
          <label for="author_${authorCount + 1}" class="form-label">Nama Penulis Ke-${authorCount + 1}</label>
          <button type="button" class="remove-author-btn text-red-600 hover:text-red-800" data-id="${authorCount}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <input type="text" id="author_${authorCount + 1}" name="author_${authorCount + 1}" class="form-control"
               value="${author}" placeholder="Contoh: John Doe">
      `;
      
      authorsContainer.appendChild(authorDiv);
      
      // Add event listener to the remove button
      authorDiv.querySelector('.remove-author-btn').addEventListener('click', function() {
        const fieldId = this.getAttribute('data-id');
        document.getElementById(`author-field-${fieldId}`).remove();
      });
    }
  });

  // Update author count on form submit to ensure all fields are included
  document.getElementById('journal-form').addEventListener('submit', function(e) {
    // Add hidden input to track total number of authors
    const totalAuthorsInput = document.createElement('input');
    totalAuthorsInput.type = 'hidden';
    totalAuthorsInput.name = 'total_authors';
    totalAuthorsInput.value = authorCount;
    this.appendChild(totalAuthorsInput);
  });
});
</script>
</div>


  <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 bg-green-600 hover:bg-green-700 text-white p-3 rounded-full shadow-lg" title="Back to Top">
    ↑
  </button>


<?php
$title = 'Unggah Jurnal | Portal Unggah Jurnal';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>
