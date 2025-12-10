<?php ob_start(); ?>
<div class="w-full py-8 px-4 sm:px-6 lg:px-8">

    <!-- Judul Halaman -->
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">
        📄 Detail Inventaris Skripsi
    </h1>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($inventory) && !empty($inventory)): ?>
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-xl border border-gray-200 p-6">

        <!-- Accordion Wrapper -->
        <div class="space-y-4" x-data="{ open1:true, open2:true }">

            <!-- INFORMASI SKRIPSI -->
            <div class="border border-gray-200 rounded-lg shadow-sm">
                <button @click="open1 = !open1"
                        class="flex justify-between items-center w-full px-5 py-4 bg-gray-50 hover:bg-gray-100 rounded-lg">
                    <span class="text-lg font-semibold text-gray-800">📘 Informasi Skripsi</span>
                    <svg :class="{'rotate-180': open1}" class="h-5 w-5 transition-transform"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open1" x-collapse>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nama Mahasiswa</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($inventory['nama_mahasiswa'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Program Studi</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($inventory['program_studi'] ?? '') ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Judul Skripsi</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($inventory['judul_skripsi'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Pengajuan</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['submission_date'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Update</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['submission_updated'] ?? '') ?></p>
                        </div>

                        <?php if (!empty($inventory['no_surat'])): ?>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">No Surat</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['no_surat']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- INFORMASI INVENTARIS -->
            <div class="border border-gray-200 rounded-lg shadow-sm">
                <button @click="open2 = !open2"
                        class="flex justify-between items-center w-full px-5 py-4 bg-gray-50 hover:bg-gray-100 rounded-lg">
                    <span class="text-lg font-semibold text-gray-800">📦 Informasi Inventaris</span>
                    <svg :class="{'rotate-180': open2}" class="h-5 w-5 transition-transform"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open2" x-collapse>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Kode Item</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($inventory['item_code'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Kode Inventaris</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($inventory['inventory_code'] ?? '') ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Nomor Panggil (DDC)</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['call_number'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Lokasi Rak</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['shelf_location'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Item</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['item_status'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Penerimaan</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['receiving_date'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sumber</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['source'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Dibuat</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['created_at'] ?? '') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Diupdate</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['updated_at'] ?? '') ?></p>
                        </div>

                        <?php if (!empty($inventory['no_surat'])): ?>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">No Surat</p>
                            <p class="font-medium"><?= htmlspecialchars($inventory['no_surat']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="<?= url('admin/inventaris') ?>"
               class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition">
                Kembali
            </a>

            <!-- Tombol Print -->
            <div class="relative group">
                <button type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 4H7v6h6V8z" clip-rule="evenodd" />
                    </svg>
                    Print
                </button>
                <div class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-1 group-hover:translate-y-0 border border-gray-200">
                    <a href="<?= url('admin/printLabel?submission_id=' . urlencode($inventory['submission_id'] ?? 0)) ?>"
                       target="_blank"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                        Print Label
                    </a>
                    <a href="<?= url('admin/printBarcode?submission_id=' . urlencode($inventory['submission_id'] ?? 0)) ?>"
                       target="_blank"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 last:border-b-0">
                        Print Barcode
                    </a>
                </div>
            </div>

            <a href="<?= url('admin/editInventaris?submission_id=' . urlencode($inventory['submission_id'] ?? 0)) ?>"
               class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                Edit
            </a>
        </div>

    </div>

    <?php else: ?>
        <div class="max-w-4xl mx-auto bg-white shadow-sm rounded-lg p-6 text-gray-700">
            Data inventaris tidak ditemukan.
        </div>
    <?php endif; ?>
</div>

<?php
$title = 'Detail Inventaris | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>
