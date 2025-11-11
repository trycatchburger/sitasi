<?php ob_start(); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Detail Pengunggahan Anda</h1>
        <p class="text-gray-600">Berikut adalah data dan file yang telah Anda unggah</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-10 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($submission)): ?>
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Informasi Umum</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Jenis Pengunggahan</p>
                        <p class="text-lg font-semibold text-gray-90 capitalize"><?= htmlspecialchars($submission['submission_type'] ?? 'skripsi') ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <?php
                        $statusClass = '';
                        switch ($submission['status']) {
                            case 'Diterima':
                                $statusClass = 'bg-green-100 text-green-800';
                                break;
                            case 'Ditolak':
                                $statusClass = 'bg-red-100 text-red-800';
                                break;
                            case 'Digantikan':
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                break;
                            default:
                                $statusClass = 'bg-gray-100 text-gray-800';
                        }
                        ?>
                        <span class="px-3 py-1 rounded-full text-sm font-medium <?= $statusClass ?>">
                            <?= htmlspecialchars($submission['status']) ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tanggal Dibuat</p>
                        <p class="text-lg font-semibold text-gray-900"><?= date('d M Y H:i', strtotime($submission['created_at'])) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tanggal Diperbarui</p>
                        <p class="text-lg font-semibold text-gray-900"><?= date('d M Y H:i', strtotime($submission['updated_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($submission['submission_type'] === 'journal'): ?>
            <!-- Informasi Khusus Jurnal -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-800">Informasi Jurnal</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nama Penulis</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['nama_mahasiswa']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Email</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['email']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Judul Jurnal</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['judul_skripsi']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tahun Publikasi</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['tahun_publikasi']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Abstrak</p>
                            <p class="text-gray-900"><?= htmlspecialchars($submission['abstract'] ?? 'Tidak tersedia') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Informasi Khusus Skripsi/Tesis -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-800">Informasi <?= $submission['submission_type'] === 'master' ? 'Tesis' : 'Skripsi' ?></h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nama Mahasiswa</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['nama_mahasiswa']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">NIM</p>
                            <p class="text-lg font-semibold text-gray-90"><?= htmlspecialchars($submission['nim'] ?? 'Tidak tersedia') ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Email</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['email']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Judul <?= $submission['submission_type'] === 'master' ? 'Tesis' : 'Skripsi' ?></p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['judul_skripsi']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Dosen Pembimbing 1</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['dosen1'] ?? 'Tidak tersedia') ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Dosen Pembimbing 2</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['dosen2'] ?? 'Tidak tersedia') ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Program Studi</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['program_studi'] ?? 'Tidak tersedia') ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tahun Publikasi</p>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($submission['tahun_publikasi']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($submission['files'])): ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-80">File yang Diunggah</h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($submission['files'] as $file): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($file['file_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($file['file_path']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?= url('public/' . $file['file_path']) ?>" target="_blank" class="text-blue-600 hover:text-blue-900">Lihat File</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 0 12 0zm-1-8a1 0 0-1 1v3a1 1 0 002 0V6a1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">Belum ada file yang diunggah untuk pengiriman ini.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($submission['keterangan']): ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-800">Keterangan</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-900"><?= htmlspecialchars($submission['keterangan']) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex justify-between mt-8">
            <a href="<?= url('user/dashboard') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Kembali ke Dashboard
            </a>
            <?php if ($submission['status'] === 'Ditolak' || $submission['is_resubmission']): ?>
                <a href="<?= url('resubmit/' . $submission['id']) ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                    Kirim Ulang
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Data tidak ditemukan</h3>
            <p class="mt-1 text-sm text-gray-500">Pengunggahan dengan ID tersebut tidak ditemukan.</p>
            <div class="mt-6">
                <a href="<?= url('user/dashboard') ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$title = 'Detail Pengunggahan | Library System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>
