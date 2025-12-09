<?php ob_start(); ?>
<div class="w-full py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Data Inventaris</h1>

    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg border border-gray-200 p-6">
        <form method="POST" action="<?= url('admin/updateInventaris') ?>" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">            
            <input type="hidden" name="submission_id" value="<?= (int)($inventory['submission_id'] ?? 0) ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title (from submissions.judul_skripsi) -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="<?= htmlspecialchars($inventory['judul_skripsi'] ?? '') ?>" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Item Code (auto-generated, readonly) -->
                <div class="md:col-span-2">
                    <label for="item_code" class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                    <input type="text" name="item_code" id="item_code" value="<?= htmlspecialchars($inventory['item_code'] ?? '') ?>" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Inventory Code -->
                <div>
                    <label for="inventory_code" class="block text-sm font-medium text-gray-700 mb-1">Inventory Code <span class="text-red-500">*</span></label>
                    <input type="text" name="inventory_code" id="inventory_code" value="<?= htmlspecialchars($inventory['inventory_code'] ?? '') ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                <!-- Call Number -->
                <div class="md:col-span-2">
                    <label for="call_number" class="block text-sm font-medium text-gray-700 mb-1">Call Number (DDC) <span class="text-red-500">*</span></label>
                    <input type="text" name="call_number" id="call_number" value="<?= htmlspecialchars($inventory['call_number'] ?? '') ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                <!-- Prodi (from submissions.program_studi) -->
                <div>
                    <label for="prodi" class="block text-sm font-medium text-gray-700 mb-1">Prodi</label>
                    <input type="text" name="prodi" id="prodi" value="<?= htmlspecialchars($inventory['program_studi'] ?? '') ?>" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Shelf Location -->
                <div>
                    <label for="shelf_location" class="block text-sm font-medium text-gray-700 mb-1">Shelf Location <span class="text-red-500">*</span></label>
                    <input type="text" name="shelf_location" id="shelf_location" value="<?= htmlspecialchars($inventory['shelf_location'] ?? '') ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                <!-- Item Status -->
                <div>
                    <label for="item_status" class="block text-sm font-medium text-gray-700 mb-1">Item Status <span class="text-red-500">*</span></label>
                    <select name="item_status" id="item_status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="Available" <?= ($inventory['item_status'] ?? '') == 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Repair" <?= ($inventory['item_status'] ?? '') == 'Repair' ? 'selected' : '' ?>>Repair</option>
                        <option value="No Loan" <?= ($inventory['item_status'] ?? '') == 'No Loan' ? 'selected' : '' ?>>No Loan</option>
                        <option value="Missing" <?= ($inventory['item_status'] ?? '') == 'Missing' ? 'selected' : '' ?>>Missing</option>
                    </select>
                </div>

                <!-- Source -->
                <div>
                    <label for="source" class="block text-sm font-medium text-gray-700 mb-1">Source <span class="text-red-500">*</span></label>
                    <select name="source" id="source" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="Buy" <?= ($inventory['source'] ?? '') == 'Buy' ? 'selected' : '' ?>>Buy</option>
                        <option value="Prize/Grant" <?= ($inventory['source'] ?? '') == 'Prize/Grant' ? 'selected' : '' ?>>Prize/Grant</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6">
                <a href="<?= url('admin/inventaris') ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$title = 'Edit Data Inventaris | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>