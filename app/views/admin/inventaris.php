<?php ob_start(); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Inventaris Skripsi</h1>
    
    <!-- Search Form -->
    <div class="mb-6">
        <form method="GET" action="">
            <div class="flex items-center space-x-2">
                <input type="text" name="search" placeholder="Cari berdasarkan nama mahasiswa, judul skripsi, atau prodi..." value="<?= htmlspecialchars($search ?? '') ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="?<?php if(isset($sort) && !empty($sort)) echo 'sort=' . $sort . '&'; if(isset($order) && !empty($order)) echo 'order=' . $order; ?>" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg border-gray-200">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; ?>sort=student_name&order=<?= isset($sort) && $sort === 'student_name' && $order === 'asc' ? 'desc' : 'asc' ?>&page=<?= $currentPage ?>" class="hover:text-green-700">
                            Nama Mahasiswa
                            <?php if (isset($sort) && $sort === 'student_name'): ?>
                                <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; ?>sort=title&order=<?= isset($sort) && $sort === 'title' && $order === 'asc' ? 'desc' : 'asc' ?>&page=<?= $currentPage ?>" class="hover:text-green-700">
                            Judul Skripsi
                            <?php if (isset($sort) && $sort === 'title'): ?>
                                <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; ?>sort=program_studi&order=<?= isset($sort) && $sort === 'program_studi' && $order === 'asc' ? 'desc' : 'asc' ?>&page=<?= $currentPage ?>" class="hover:text-green-700">
                            Prodi
                            <?php if (isset($sort) && $sort === 'program_studi'): ?>
                                <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; ?>sort=inventaris_status&order=<?= isset($sort) && $sort === 'inventaris_status' && $order === 'asc' ? 'desc' : 'asc' ?>&page=<?= $currentPage ?>" class="hover:text-green-700">
                            Status Inventaris
                            <?php if (isset($sort) && $sort === 'inventaris_status'): ?>
                                <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($submissions) && is_array($submissions)): ?>
                    <?php $counter = ($currentPage - 1) * 10 + 1; ?>
                    <?php foreach ($submissions as $submission): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $counter++ ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($submission['nama_mahasiswa']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="<?= htmlspecialchars($submission['judul_skripsi']) ?>"><?= htmlspecialchars($submission['judul_skripsi']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($submission['program_studi']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (strpos($submission['inventaris_status'], 'Sudah Ada') !== false): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?= htmlspecialchars($submission['inventaris_status']) ?></span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><?= htmlspecialchars($submission['inventaris_status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if (strpos($submission['inventaris_status'], 'Sudah Ada') !== false): ?>
                                    <a href="#" class="text-blue-600 hover:text-blue-900">Detail</a>
                                <?php else: ?>
                                    <a href="#" class="text-green-600 hover:text-green-900">Tambah</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada data skripsi yang diterima untuk ditampilkan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($totalPages) && $totalPages > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium"><?= ($currentPage - 1) * 10 + 1 ?></span> to
            <span class="font-medium"><?= min($currentPage * 10, $totalResults) ?></span> of
            <span class="font-medium"><?= $totalResults ?></span> results
        </div>
        <div class="flex space-x-2">
            <?php if ($currentPage > 1): ?>
                <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; if(isset($sort)) echo 'sort=' . $sort . '&'; if(isset($order)) echo 'order=' . $order . '&'; ?>page=<?= $currentPage - 1 ?>" class="px-3 py-1 rounded-md bg-white border border-gray-30 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i >= max(1, $currentPage - 2) && $i <= min($totalPages, $currentPage + 2)): ?>
                    <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; if(isset($sort)) echo 'sort=' . $sort . '&'; if(isset($order)) echo 'order=' . $order . '&'; ?>page=<?= $i ?>" class="px-3 py-1 rounded-md <?php echo ($i == $currentPage) ? 'bg-green-600 text-white' : 'bg-white border border-gray-30 text-gray-700 hover:bg-gray-50'; ?> text-sm font-medium"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; if(isset($sort)) echo 'sort=' . $sort . '&'; if(isset($order)) echo 'order=' . $order . '&'; ?>page=<?= $currentPage + 1 ?>" class="px-3 py-1 rounded-md bg-white border border-gray-30 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$title = 'Inventaris Skripsi | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>