<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Admin</title>
    <link rel="icon" type="image/png" href="<?= url('public/images/icons/favicon.png') ?>">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-green-50 to-white text-gray-800 antialiased min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Ubah Password</h2>
            <p class="text-gray-600">Ganti password akun admin Anda</p>
        </div>
        
        <!-- Display error messages if any -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Display success messages if any -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= url('admin/change_password') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? $_SESSION['csrf_token'] ?? ''); ?>">
            
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                <input type="password" name="current_password" id="current_password"
                       required
                       placeholder="Masukkan password lama"
                       class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input type="password" name="new_password" id="new_password"
                       required
                       placeholder="Masukkan password baru (Minimal 8 karakter)"
                       class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            
            <div>
                <label for="confirm_new_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_new_password" id="confirm_new_password"
                       required
                       placeholder="Ulangi password baru"
                       class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            
            <button type="submit"
                    class="w-full bg-green-700 text-white py-2 rounded-md text-sm font-semibold hover:bg-green-800 transition">
                Ubah Password
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="<?= url('admin/dashboard') ?>" class="text-green-700 hover:text-green-900 text-sm font-medium">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>