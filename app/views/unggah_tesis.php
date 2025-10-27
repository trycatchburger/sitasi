<?php ob_start(); ?>
      
<div class="w-full bg-green-50 min-h-screen">
    <div class="w-full px-4">

        <div class="text-center mb-8 bg-white rounded-xl shadow-md p-8 border-b-4 border-green-600">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">UNGGAH TESIS MAGISTER</h1>
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

                        <form action="<?php echo url('submission/create_master'); ?>" method="post" enctype="multipart/form-data">
                            <!-- Personal Information Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informasi Mahasiswa</h2>
                                
                                <div class="row">
                                    <div class="col md:col-6">
                                        <div class="form-group">
                                            <label for="nama_mahasiswa" class="form-label">Nama Lengkap</label>
                                            <input type="text" id="nama_mahasiswa" name="nama_mahasiswa" required
                                                class="form-control"
                                                value="<?= isset($old_data['nama_mahasiswa']) ? htmlspecialchars($old_data['nama_mahasiswa']) : '' ?>">
                                            <p class="text-xs text-gray-500 mt-1">Contoh: Iis Rahayu. Gunakan huruf kapital di awal kata.</p>
                                            
                                            <?php if (isset($errors['nama_mahasiswa'])): ?>
                                                <div class="text-red-500 text-sm mt-1">
                                                    <?php foreach ($errors['nama_mahasiswa'] as $error): ?>
                                                        <div><?= htmlspecialchars($error) ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col md:col-6">
                                        <div class="form-group">
                                            <label for="nim" class="form-label">NIM</label>
                                            <input type="text" id="nim" name="nim" required
                                                class="form-control"
                                                value="<?= isset($old_data['nim']) ? htmlspecialchars($old_data['nim']) : '' ?>">
                                            <?php if (isset($errors['nim'])): ?>
                                                <div class="text-red-500 text-sm mt-1">
                                                    <?php foreach ($errors['nim'] as $error): ?>
                                                        <div><?= htmlspecialchars($error) ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" required
                                        class="form-control"
                                        value="<?= isset($old_data['email']) ? htmlspecialchars($old_data['email']) : '' ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="text-red-500 text-sm mt-1">
                                            <?php foreach ($errors['email'] as $error): ?>
                                                <div><?= htmlspecialchars($error) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Thesis Information Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informasi Tesis</h2>
                                
                                <div class="form-group">
                                    <label for="judul_skripsi" class="form-label">Judul Tesis</label>
                                    <input type="text" id="judul_skripsi" name="judul_skripsi" required class="form-control"
                                           value="<?= isset($old_data['judul_skripsi']) ? htmlspecialchars($old_data['judul_skripsi']) : '' ?>">
                                    <p class="text-xs text-gray-500 mt-1">Contoh: Analisis Kinerja Sistem Informasi Manajemen. Gunakan huruf kapital di awal kata.</p>
                                    <?php if (isset($errors['judul_skripsi'])): ?>
                                        <div class="text-red-50 text-sm mt-1">
                                            <?php foreach ($errors['judul_skripsi'] as $error): ?>
                                                <div><?= htmlspecialchars($error) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col md:col-6">
                                        <div class="form-group">
                                            <label for="dosen1" class="form-label">Dosen Pembimbing 1</label>
                                            <input type="text" id="dosen1" name="dosen1" required
                                                class="form-control"
                                                value="<?= isset($old_data['dosen1']) ? htmlspecialchars($old_data['dosen1']) : '' ?>">
                                            <?php if (isset($errors['dosen1'])): ?>
                                                <div class="text-red-500 text-sm mt-1">
                                                    <?php foreach ($errors['dosen1'] as $error): ?>
                                                        <div><?= htmlspecialchars($error) ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col md:col-6">
                                        <div class="form-group">
                                            <label for="dosen2" class="form-label">Dosen Pembimbing 2</label>
                                            <input type="text" id="dosen2" name="dosen2" required
                                                class="form-control"
                                                value="<?= isset($old_data['dosen2']) ? htmlspecialchars($old_data['dosen2']) : '' ?>">
                                            <?php if (isset($errors['dosen2'])): ?>
                                                <div class="text-red-500 text-sm mt-1">
                                                    <?php foreach ($errors['dosen2'] as $error): ?>
                                                        <div><?= htmlspecialchars($error) ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col md:col-6">
                                        <div class="form-group">
                                            <label for="program_studi" class="form-label">Program Studi</label>
                                            <select id="program_studi" name="program_studi" required
                                                    class="form-control form-select">
                                                <option value="Magister Manajemen Pendidikan Islam">Magister Manajemen Pendidikan Islam</option>
                                            </select>
                                            <?php if (isset($errors['program_studi'])): ?>
                                                <div class="text-red-500 text-sm mt-1">
                                                    <?php foreach ($errors['program_studi'] as $error): ?>
                                                        <div><?= htmlspecialchars($error) ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col md:col-6">
                                        <div class="form-group">
                                            <label for="tahun_publikasi" class="form-label">Tahun Publikasi</label>
                                            <select id="tahun_publikasi" name="tahun_publikasi" required class="form-control form-select">
                                                <option value="">Pilih Tahun</option>
                                                <?php
                                                $startYear = 2025; // Tahun awal tetap
                                                $currentYear = date('Y'); // Tahun sekarang
                                                for ($year = $startYear; $year <= $currentYear; $year++): ?>
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
                            </div>

                            <!-- Document Upload Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Unggah File</h2>
                                
                                <div class="space-y-6">
                                    <div class="form-group">
                                        <label for="file_cover" class="form-label">Cover Tesis (.pdf)</label>
                                        <input type="file" id="file_cover" name="file_cover" required
                                            class="form-control" accept=".pdf">
                                        <div class="form-text">(Format Nama File: COVER_NIM_NAMA)</div>
                                        <?php if (isset($errors['file_cover'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['file_cover'] as $error): ?>
                                                    <div><?= htmlspecialchars($error) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="file_bab1" class="form-label">Cover s/d Bab I & Daftar Pustaka Tesis (.pdf)</label>
                                        <input type="file" id="file_bab1" name="file_bab1" required
                                            class="form-control" accept=".pdf">
                                        <div class="form-text">(Format Nama File: NIM_BAB1_DAFTAR PUSTAKA)</div>
                                        <?php if (isset($errors['file_bab1'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['file_bab1'] as $error): ?>
                                                    <div><?= htmlspecialchars($error) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="file_bab2" class="form-label">Bab II s/d Bab Terakhir Tesis (.pdf)</label>
                                        <input type="file" id="file_bab2" name="file_bab2" required
                                            class="form-control" accept=".pdf">
                                        <div class="form-text">(Format Nama File: NIM_BAB II_SAMPAI BAB TERAKHIR)</div>
                                        <?php if (isset($errors['file_bab2'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['file_bab2'] as $error): ?>
                                                    <div><?= htmlspecialchars($error) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="file_doc" class="form-label">Cover s/d Daftar Pustaka Tesis (.doc, .docx)</label>
                                        <input type="file" id="file_doc" name="file_doc" required class="form-control" accept=".doc,.docx">
                                        <div class="form-text">(Format Nama File: NIM_TESIS. Ukuran maksimum file: 10240KB.)</div>
                                        <?php if (isset($errors['file_doc'])): ?>
                                            <div class="text-red-500 text-sm mt-1">
                                                <?php foreach ($errors['file_doc'] as $error): ?>
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
                <h4 class="font-semibold text-green-80 text-lg flex items-center gap-2 mb-2">
                    ⏰ Batas Waktu Unggah
                </h4>
                <p class="text-gray-700 text-sm">Unggah tesis paling lambat <strong>31 Desember 2025</strong>.</p>
                </div>

                <!-- Alur Unggah -->
                <div class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition">
                <h4 class="font-semibold text-green-800 text-lg flex items-center gap-2 mb-2">
                    📄 Alur Unggah
                </h4>
                <ol class="list-decimal pl-5 text-gray-700 text-sm space-y-1">
                    <li>Isi data dan unggah berkas tesis (PDF dan DOC/DOCX).</li>
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
                    <h4 class="font-semibold text-green-80 text-lg flex items-center gap-2 mb-3">
                        📘 Format Dokumen
                    </h4>

                <div class="text-gray-700 text-sm space-y-4">

                    <!-- File 1 -->
                    <div class="border border-green-200 rounded-md p-3 bg-green-50/50">
                        <p><strong>• File 1 – Format:</strong> PDF</p>
                        <p class="ml-4">Nama file: <code class="bg-gray-100 px-1 rounded text-green-800">COVER_NIM_NAMA</code></p>
                        <p class="ml-4 font-semibold mt-1">Isi:</p>
                        <ul class="list-disc pl-8">
                            <li>Cover Tesis</li>
                        </ul>
                    </div>

                    <!-- File 2 -->
                    <div class="border border-green-200 rounded-md p-3 bg-green-50/50">
                        <p><strong>• File 2 – Format:</strong> PDF</p>
                        <p class="ml-4">Nama file: <code class="bg-gray-100 px-1 rounded text-green-800">NIM_BAB1_DAFTAR PUSTAKA</code></p>
                        <p class="ml-4 font-semibold mt-1">Isi:</p>
                        <ul class="list-disc pl-8">
                            <li>Cover s/d Bab I & Daftar Pustaka Tesis</li>
                        </ul>
                    </div>

                    <!-- File 3 -->
                    <div class="border border-green-200 rounded-md p-3 bg-green-50/50">
                        <p><strong>• File 3 – Format:</strong> PDF</p>
                        <p class="ml-4">Nama file: <code class="bg-gray-100 px-1 rounded text-green-800">NIM_BAB II_SAMPAI BAB TERAKHIR</code></p>
                        <p class="ml-4 font-semibold mt-1">Isi:</p>
                        <ul class="list-disc pl-8">
                            <li>Bab II s/d Bab Terakhir Tesis</li>
                        </ul>
                    </div>

                    <!-- File 4 -->
                    <div class="border border-green-200 rounded-md p-3 bg-green-50/50">
                    <p><strong>• File 4 – Format:</strong> MS Word (.doc/.docx)</p>
                    <p class="ml-4">Nama file: <code class="bg-gray-100 px-1 rounded text-green-800">NIM_TESIS</code></p>
                    <p class="ml-4 font-semibold mt-1">Isi:</p>
                    <ul class="list-disc pl-8">
                        <li>Cover s/d Daftar Pustaka Tesis</li>
                    </ul>
                    </div>

                </div>
                </div>


    <!-- Verifikasi -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition">
      <h4 class="font-semibold text-green-80 text-lg flex items-center gap-2 mb-2">
        📩 Verifikasi
      </h4>
      <p class="text-gray-700 text-sm">
        Cek email secara berkala untuk menerima hasil verifikasi unggahan Anda.
      </p>
    </div>
                                            

  </section>
</div>
            </div>
            </div>

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
});
</script>
</div>


  <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 bg-green-600 hover:bg-green-700 text-white p-3 rounded-full shadow-lg" title="Back to Top">
    ↑
 </button>



<?php
$title = 'Unggah Tesis | Portal Unggah Tesis Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>