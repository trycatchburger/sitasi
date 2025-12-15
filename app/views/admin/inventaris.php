<?php ob_start(); ?>
<div class="w-full py-8 px-4 sm:px-6 lg:px-8">
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

    <form method="POST" action="<?= url('admin/bulkPrintInventaris') ?>" id="bulk-print-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="overflow-x-auto bg-white shadow-md rounded-lg border-gray-200 mb-4">
            <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12"><input type="checkbox" id="select-all" class="bulk-select-all"></th>
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
                        <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; ?>sort=serial_number&order=<?= isset($sort) && $sort === 'serial_number' && $order === 'asc' ? 'desc' : 'asc' ?>&page=<?= $currentPage ?>" class="hover:text-green-700">
                            No Surat
                            <?php if (isset($sort) && $sort === 'serial_number'): ?>
                                <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="?<?php if(isset($search) && !empty($search)) echo 'search=' . urlencode($search) . '&'; ?>sort=item_code&order=<?= isset($sort) && $sort === 'item_code' && $order === 'asc' ? 'desc' : 'asc' ?>&page=<?= $currentPage ?>" class="hover:text-green-700">
                            Kode Inventaris
                            <?php if (isset($sort) && $sort === 'item_code'): ?>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><input type="checkbox" name="selected_ids[]" value="<?= $submission['id'] ?>" class="bulk-select"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $counter++ ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($submission['nama_mahasiswa']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="<?= htmlspecialchars($submission['judul_skripsi']) ?>"><?= htmlspecialchars($submission['judul_skripsi']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($submission['program_studi']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($submission['serial_number'] ?? '') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= !empty($submission['item_code']) ? htmlspecialchars($submission['item_code']) : 'Belum Ada' ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if (!empty($submission['item_code'])): ?>
                                    <a href="<?= url('admin/editInventaris') . '?submission_id=' . $submission['id'] ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <a href="<?= url('admin/detailInventaris') . '?submission_id=' . $submission['id'] ?>" class="text-blue-600 hover:text-blue-900">Detail</a>
                                    <a href="<?= url('admin/printBarcode') . '?submission_id=' . $submission['id'] ?>" class="text-green-600 hover:text-green-900">Cetak Barcode</a>
                                <?php else: ?>
                                    <a href="<?= url('admin/tambahInventaris') . '?submission_id=' . $submission['id'] ?>" class="text-green-600 hover:text-green-900">Tambah</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada data skripsi yang diterima untuk ditampilkan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="flex justify-between items-center mt-4 px-6 py-3 border-t border-gray-200">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed" id="bulk-print-btn" disabled>Bulk Print Barcode</button>
            <span id="selected-count" class="text-sm text-gray-600">0 item terpilih</span>
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
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bulk select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    // Individual checkboxes
    const checkboxes = document.querySelectorAll('.bulk-select');
    // Bulk print button
    const bulkPrintBtn = document.getElementById('bulk-print-btn');
    // Selected count display
    const selectedCount = document.getElementById('selected-count');
    // Bulk print form
    const bulkPrintForm = document.getElementById('bulk-print-form');

    // Update selected count and button state
    function updateSelectionStatus() {
        const checkedCount = document.querySelectorAll('.bulk-select:checked').length;
        selectedCount.textContent = checkedCount + ' item terpilih';
        bulkPrintBtn.disabled = checkedCount === 0;
    }

    // Initialize selection status on page load
    updateSelectionStatus();

    // Select/deselect all checkboxes
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionStatus();
        });
    }

    // Individual checkbox change event
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox state based on individual selections
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && anyChecked;
            }
            
            updateSelectionStatus();
        });
    });

    // Prevent form submission if no items are selected
    bulkPrintForm.addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.bulk-select:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Silakan pilih setidaknya satu item untuk dicetak.');
            return false;
        }
    });
});
</script>
<?php
$title = 'Inventaris Skripsi | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>