<?php ob_start(); ?>
<div class="w-full py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Tambah Data Inventaris</h1>
    
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg border border-gray-200 p-6">
        <form method="POST" action="<?= url('admin/simpanInventaris') ?>" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title (from submissions.judul_skripsi) -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="hidden" name="submission_id" value="<?= (int)($submission['id'] ?? 0) ?>">
                    <input type="text" name="title" id="title" value="<?= htmlspecialchars($submission['judul_skripsi'] ?? '') ?>" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <!-- Item Code (auto-generated, hidden) -->
                <input type="hidden" name="item_code" id="item_code" value="<?= htmlspecialchars(($submission['id'] ?? '') . uniqid()) ?>">
                
                <!-- Inventory Code -->
                <div>
                    <label for="inventory_code" class="block text-sm font-medium text-gray-700 mb-1">Inventory Code <span class="text-red-500">*</span></label>
                    <input type="text" name="inventory_code" id="inventory_code" required
                           placeholder="cth: 7974"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <!-- Call Number -->
                <div class="md:col-span-2">
                    <label for="call_number" class="block text-sm font-medium text-gray-700 mb-1">Call Number (DDC) <span class="text-red-500">*</span></label>
                    <input type="text" name="call_number" id="call_number" required
                           placeholder="cth: 658.07 MEL p"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <!-- Prodi (from submissions.program_studi) -->
                <div>
                    <label for="prodi" class="block text-sm font-medium text-gray-700 mb-1">Prodi <span class="text-red-500">*</span></label>
                    <input type="text" name="prodi" id="prodi" value="<?= htmlspecialchars($submission['program_studi'] ?? '') ?>" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <!-- Shelf Location -->
                <div>
                    <label for="shelf_location" class="block text-sm font-medium text-gray-700 mb-1">Shelf Location <span class="text-red-500">*</span></label>
                    <input type="text" name="shelf_location" id="shelf_location" required
                           placeholder="Lokasi rak penyimpanan"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <!-- Item Status -->
                <div>
                    <label for="item_status" class="block text-sm font-medium text-gray-700 mb-1">Item Status <span class="text-red-500">*</span></label>
                    <select name="item_status" id="item_status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Pilih Status</option>
                        <option value="Available">Available</option>
                        <option value="Repair">Repair</option>
                        <option value="No Loan">No Loan</option>
                        <option value="Missing">Missing</option>
                    </select>
                </div>
                
                <!-- Receiving Date (from submissions.updated_at) -->
                <div>
                    <label for="receiving_date" class="block text-sm font-medium text-gray-700 mb-1">Receiving Date <span class="text-red-500">*</span></label>
                    <input type="date" name="receiving_date" id="receiving_date" value="<?= htmlspecialchars(date('Y-m-d', strtotime($submission['updated_at'] ?? date('Y-m-d')))) ?>" readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <!-- Source -->
                <div>
                    <label for="source" class="block text-sm font-medium text-gray-700 mb-1">Source <span class="text-red-500">*</span></label>
                    <select name="source" id="source" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Pilih Sumber</option>
                        <option value="Buy">Buy</option>
                        <option value="Prize/Grant">Prize/Grant</option>
                    </select>
                </div>
            
            <div class="flex justify-end space-x-4 pt-6">
                <a href="<?= url('admin/inventaris') ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$title = 'Tambah Data Inventaris | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>