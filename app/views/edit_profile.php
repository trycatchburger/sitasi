<?php ob_start(); ?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-80 mb-2">Edit Profil</h1>
        <p class="text-gray-600">Perbarui informasi akun Anda</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/200/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
        <div class="px-6 py-5 bg-gradient-to-r from-blue-500 to-indigo-600">
            <h2 class="text-xl font-semibold text-white">Informasi Pribadi</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="<?= url('user/update_profile') ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">ID Anggota <span class="text-gray-500 text-xs">(hanya baca)</span></label>
                        <input type="text" name="id_member" value="<?= htmlspecialchars($user['id_member']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100 cursor-not-allowed" readonly>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" required>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="w-full px-4 py-3 border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Program Studi</label>
                        <input type="text" name="prodi" value="<?= htmlspecialchars($user['prodi'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Tipe Anggota <span class="text-gray-500 text-xs">(hanya baca)</span></label>
                        <input type="text" name="tipe_member" value="<?= htmlspecialchars($user['tipe_member'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100 cursor-not-allowed" readonly>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Anggota Sejak <span class="text-gray-500 text-xs">(hanya baca)</span></label>
                        <input type="text" name="member_since" value="<?= htmlspecialchars($user['member_since'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-10 cursor-not-allowed" readonly>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Kedaluwarsa Keanggotaan <span class="text-gray-500 text-xs">(hanya baca)</span></label>
                        <input type="text" name="expired" value="<?= htmlspecialchars($user['expired'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100 cursor-not-allowed" readonly>
                    </div>
                </div>
                
                <div class="mt-8 flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="<?= url('user/profile') ?>" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition duration-200 text-center font-medium shadow-sm">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 text-center font-medium shadow-md transform hover:scale-[1.02]">
                        Perbarui Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$title = 'Edit Profil | Sistem Perpustakaan';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>
