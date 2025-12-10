
<?php ob_start(); ?>
<div class="w-full py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Detail Inventaris Skripsi</h1>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($inventory) && !empty($inventory)): ?>
        <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg border border-gray-200 p-6 mb-6">
            <div class="space-y-8 mb-8">
                <!-- Informasi Skripsi Section -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.47 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2">Informasi Skripsi</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Mahasiswa</label>
                            <p class="text-gray-900 font-medium break-words"><?= htmlspecialchars($inventory['nama_mahasiswa'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Program Studi</label>
                            <p class="text-gray-900 font-medium break-words"><?= htmlspecialchars($inventory['program_studi'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Judul Skripsi</label>
                            <p class="text-gray-900 font-medium break-words"><?= htmlspecialchars($inventory['judul_skripsi'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Pengajuan</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['submission_date'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Update</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['submission_updated'] ?? '') ?></p>
                        </div>
                        <!-- Add No Surat field if it exists in the data -->
                        <?php if (isset($inventory['no_surat']) && !empty($inventory['no_surat'])): ?>
                        <div class="flex flex-col md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">No Surat</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['no_surat'] ?? '') ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            
                <!-- Informasi Inventaris Section -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2">Informasi Inventaris</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Kode Item</label>
                            <p class="text-gray-900 font-medium break-words"><?= htmlspecialchars($inventory['item_code'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Kode Inventaris</label>
                            <p class="text-gray-900 font-medium break-words"><?= htmlspecialchars($inventory['inventory_code'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nomor Panggil (DDC)</label>
                            <p class="text-gray-900 font-medium break-words"><?= htmlspecialchars($inventory['call_number'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Lokasi Rak</label>
                            <p class="text-gray-900 font-medium break-words"><?= htmlspecialchars($inventory['shelf_location'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Status Item</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['item_status'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Penerimaan</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['receiving_date'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Sumber</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['source'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Dibuat</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['created_at'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Diupdate</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['updated_at'] ?? '') ?></p>
                        </div>
                        <!-- Add No Surat field if it exists in the data -->
                        <?php if (isset($inventory['no_surat']) && !empty($inventory['no_surat'])): ?>
                        <div class="flex flex-col md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">No Surat</label>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($inventory['no_surat'] ?? '') ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="<?= url('admin/inventaris') ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                    Kembali
                </a>
                <a href="<?= url('admin/editInventaris?submission_id=' . urlencode($inventory['submission_id'] ?? 0)) ?>" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    Edit
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg border border-gray-20 p-6">
            <p class="text-gray-700">Data inventaris tidak ditemukan.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$title = 'Detail Inventaris | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>
