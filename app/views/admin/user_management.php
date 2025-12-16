<?php ob_start(); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
     <a href="<?= url('admin/importDataAnggota') ?>" class="btn btn-primary text-white flex items-center px-4 py-2 rounded-lg shadow-sm hover:bg-blue-600 transition">
      <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 1112 0v1H3v-1z"></path>
      </svg>
      Import Data Anggota
    </a>
    </div>

    <?php if (isset($_SESSION['success_message']) && !isset($_GET['reset_success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><?= htmlspecialchars($_SESSION['success_message']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><?= htmlspecialchars($_SESSION['error_message']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Library Card Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                // Use the users array directly from the controller
                $displayUsers = !empty($users) ? $users : [];
                
                foreach ($displayUsers as $user): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['library_card_number']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['created_at']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if (isset($user['status']) && $user['status'] === 'suspended'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                        <?php elseif (isset($user['status']) && $user['status'] === 'No Account'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No Account</span>
                        <?php elseif (isset($user['status']) && $user['status'] === 'active'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><?= htmlspecialchars($user['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <?php if ($user['has_account']): ?>
                            <!-- Show user management actions only for users with accounts -->
                            <form method="POST" action="<?= url('admin/resetUserPassword') ?>" class="inline mr-1" id="resetForm_<?= $user['id'] ?>">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="button" onclick="showResetConfirmation(<?= $user['id'] ?>)" class="text-blue-600 hover:text-blue-80 font-medium cursor-pointer">
                                    Reset Password
                                </button>
                            </form>
                            
                            <form method="POST" action="<?= url('admin/suspendUser') ?>" class="inline mr-1" id="suspendForm_<?= $user['id'] ?>">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <?php if (isset($user['status']) && $user['status'] === 'suspended'): ?>
                                    <button type="button" onclick="showSuspendConfirmation(<?= $user['id'] ?>, 'activate')" class="text-green-600 hover:text-green-800 font-medium cursor-pointer">
                                        Activate
                                    </button>
                                <?php else: ?>
                                    <button type="button" onclick="showSuspendConfirmation(<?= $user['id'] ?>, 'suspend')" class="text-yellow-600 hover:text-yellow-800 font-medium cursor-pointer">
                                        Suspend
                                    </button>
                                <?php endif; ?>
                            </form>
                            
                            <form method="POST" action="<?= url('admin/deleteUser') ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this user account?');">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-80 font-medium">
                                    Delete
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- For members without accounts, show a message or registration reminder -->
                            <span class="text-gray-500 text-sm">No account</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6 text-center">
    <a href="<?= url('admin/dashboard') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition duration-200 inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        Back to Admin Dashboard
    </a>
</div>
<?php
$title = 'User Management | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>

<!-- Password Reset Confirmation Modal -->
<div id="passwordResetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900 mt-4">Reset Kata Sandi Berhasil</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-600">Kata sandi pengguna telah berhasil direset.</p>
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200 text-center">
                    <p class="text-sm font-medium text-gray-700 mb-2">Kata Sandi Baru:</p>
                    <p id="newPasswordDisplay" class="text-xl font-bold text-blue-700 break-all tracking-wider bg-white p-3 rounded-md border border-gray-200"></p>
                    <p class="text-xs text-gray-500 mt-3">Silakan bagikan ini kepada pengguna secara aman</p>
                </div>
            </div>
            <div class="items-center px-4 py-3 mt-6">
                <button id="closeModalBtn" class="px-6 py-2 bg-blue-600 text-white text-base font-medium rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 transition duration-200 transform hover:scale-105">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal for Suspend/Activate User -->
<div id="confirmSuspendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" style="display: none;">
    <div class="relative top-40 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 id="suspendModalTitle" class="text-lg leading-6 font-bold text-gray-900 mt-4">Konfirmasi Suspensi Akun</h3>
            <div class="mt-2 px-7 py-3">
                <p id="suspendModalDesc" class="text-sm text-gray-600">Apakah Anda yakin ingin mensuspend akun pengguna ini?</p>
                <p class="text-xs text-gray-500 mt-2">Tindakan ini akan mempengaruhi akses pengguna ke sistem.</p>
            </div>
            <div class="items-center px-4 py-3 mt-4 space-x-3">
                <button id="confirmSuspendBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition duration-200 transform hover:scale-105">
                    Ya, Suspend
                </button>
                <button id="cancelSuspendBtn" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-lg shadow-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition duration-200 transform hover:scale-105">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal for Reset Password -->
<div id="confirmResetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" style="display: none;">
    <div class="relative top-40 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900 mt-4">Konfirmasi Reset Kata Sandi</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-600">Apakah Anda yakin ingin mereset kata sandi pengguna ini?</p>
                <p class="text-xs text-gray-500 mt-2">Kata sandi akan direset menjadi nilai default dan pemberitahuan akan dikirim ke pengguna.</p>
            </div>
            <div class="items-center px-4 py-3 mt-4 space-x-3">
                <button id="confirmResetBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition duration-200 transform hover:scale-105">
                    Ya, Reset
                </button>
                <button id="cancelResetBtn" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-lg shadow-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition duration-200 transform hover:scale-105">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a reset password success message in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const resetSuccess = urlParams.get('reset_success');
    const newPassword = urlParams.get('new_password');
    
    if (resetSuccess === '1' && newPassword) {
        // Show modal with new password
        document.getElementById('newPasswordDisplay').textContent = newPassword;
        document.getElementById('passwordResetModal').classList.remove('hidden');
        document.getElementById('passwordResetModal').style.display = 'block';
    }
    
    // Close modal button event
    document.getElementById('closeModalBtn').addEventListener('click', function() {
        document.getElementById('passwordResetModal').classList.add('hidden');
        document.getElementById('passwordResetModal').style.display = 'none';
        // Remove query parameters from URL
        window.history.replaceState({}, document.title, window.location.pathname + window.location.search);
    });
    
    // Confirmation modal functionality
    let userIdToReset = null;
    let userIdToSuspend = null;
    let suspendAction = null;
    
    window.showResetConfirmation = function(userId) {
        userIdToReset = userId;
        document.getElementById('confirmResetModal').classList.remove('hidden');
        document.getElementById('confirmResetModal').style.display = 'block';
    };
    
    window.showSuspendConfirmation = function(userId, action) {
        userIdToSuspend = userId;
        suspendAction = action;
        const modal = document.getElementById('confirmSuspendModal');
        const title = document.getElementById('suspendModalTitle');
        const description = document.getElementById('suspendModalDesc');
        const confirmBtn = document.getElementById('confirmSuspendBtn');
        
        if (action === 'suspend') {
            title.textContent = 'Konfirmasi Suspensi Akun';
            description.textContent = 'Apakah Anda yakin ingin mensuspend akun pengguna ini?';
            confirmBtn.textContent = 'Ya, Suspend';
            confirmBtn.className = 'px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition duration-200 transform hover:scale-105';
        } else {
            title.textContent = 'Konfirmasi Aktivasi Akun';
            description.textContent = 'Apakah Anda yakin ingin mengaktifkan kembali akun pengguna ini?';
            confirmBtn.textContent = 'Ya, Aktifkan';
            confirmBtn.className = 'px-4 py-2 bg-green-600 text-white text-base font-medium rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300 transition duration-200 transform hover:scale-105';
        }
        
        modal.classList.remove('hidden');
        modal.style.display = 'block';
    };
    
    document.getElementById('confirmResetBtn').addEventListener('click', function() {
        if (userIdToReset) {
            document.getElementById('resetForm_' + userIdToReset).submit();
        }
    });
    
    document.getElementById('confirmSuspendBtn').addEventListener('click', function() {
        if (userIdToSuspend && suspendAction) {
            const form = document.getElementById('suspendForm_' + userIdToSuspend);
            // Add action input to form if it doesn't exist
            let actionInput = form.querySelector('input[name="action"]');
            if (!actionInput) {
                actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = suspendAction;
                form.appendChild(actionInput);
            } else {
                actionInput.value = suspendAction;
            }
            form.submit();
        }
    });
    
    document.getElementById('cancelResetBtn').addEventListener('click', function() {
        document.getElementById('confirmResetModal').classList.add('hidden');
        document.getElementById('confirmResetModal').style.display = 'none';
        userIdToReset = null;
    });
    
    document.getElementById('cancelSuspendBtn').addEventListener('click', function() {
        document.getElementById('confirmSuspendModal').classList.add('hidden');
        document.getElementById('confirmSuspendModal').style.display = 'none';
        userIdToSuspend = null;
        suspendAction = null;
    });
});
</script>