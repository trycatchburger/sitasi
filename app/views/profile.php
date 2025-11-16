<?php ob_start(); ?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Profil</h1>
        <p class="text-gray-600">Kelola informasi akun Anda</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 0-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">ID Anggota</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['id_member']) ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">Nama</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">Email</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['email'] ?? 'Tidak disediakan') ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">Nomor Telepon</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['no_hp'] ?? 'Tidak disediakan') ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">Program Studi</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['prodi'] ?? 'Tidak disediakan') ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">Tipe Anggota</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['tipe_member'] ?? 'Tidak disediakan') ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">Anggota Sejak</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['member_since'] ?? 'Tidak disediakan') ?></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 bg-gray-50 text-sm font-medium text-gray-700 whitespace-nowrap w-1/3">Kedaluwarsa Keanggotaan</td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($user['expired'] ?? 'Tidak disediakan') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4">
        <a href="<?= url('user/edit_profile') ?>" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-70 hover:to-indigo-800 text-white rounded-lg transition duration-200 text-center font-medium shadow-md transform hover:scale-[1.02]">
            Perbarui Profil
        </a>
        <a href="<?= url('user/dashboard') ?>" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition duration-200 text-center font-medium shadow-sm">
            Kembali ke Dashboard
        </a>
    </div>
</div>

<?php
$title = 'Profile | Library System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>