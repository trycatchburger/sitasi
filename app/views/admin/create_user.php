<?php ob_start(); ?>
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Create User Account</h1>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors) && is_array($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Validation Errors:</strong>
            <ul class="list-disc pl-5 mt-2">
                <?php foreach ($errors as $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $fieldError): ?>
                        <li><?= htmlspecialchars($fieldError) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div>
            <label for="library_card_number" class="block text-sm font-medium text-gray-700 mb-1">Library Card Number</label>
            <input
                type="text"
                id="library_card_number"
                name="library_card_number"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter library card number"
                value="<?= isset($_POST['library_card_number']) ? htmlspecialchars($_POST['library_card_number']) : '' ?>"
            >
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter full name"
                value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
            >
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter email address"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
            >
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter password"
            >
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Confirm password"
            >
        </div>

        <div class="pt-4">
            <button
                type="submit"
                class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-70 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                Create User Account
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <a href="<?= url('admin/userManagement') ?>" class="text-green-600 hover:text-green-800 text-sm font-medium">
            ← Back to User Management
        </a>
    </div>
</div>

<?php
$title = 'Create User | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>