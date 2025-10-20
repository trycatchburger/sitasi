<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8 max-w-md">
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Buat Admin Baru</h2>
    
    <?php if (isset($error)): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($errors) && is_array($errors)): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
        <strong>Validation Errors:</strong>
        <ul class="list-disc pl-5 mt-2">
          <?php foreach ($errors as $field => $fieldErrors): ?>
            <?php foreach ($fieldErrors as $fieldError): ?>
              <li><?= htmlspecialchars($fieldError) ?></li>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    
    <form method="POST" action="<?= url('admin/create') ?>" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nama Pengguna</label>
        <input
          type="text"
          id="username"
          name="username"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Masukkan nama pengguna"
          value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
        >
        <?php if (isset($errors['username'])): ?>
          <div class="text-red-500 text-sm mt-1">
            <?php foreach ($errors['username'] as $error): ?>
              <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          required 
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Masukkan kata sandi"
          minlength="6"
        >
        <p class="mt-1 text-xs text-gray-500">Kata sandi harus terdiri dari minimal 6 karakter.</p>
        <?php if (isset($errors['password'])): ?>
          <div class="text-red-500 text-sm mt-1">
            <?php foreach ($errors['password'] as $error): ?>
              <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      
      <div>
        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
        <input 
          type="password" 
          id="confirm_password" 
          name="confirm_password" 
          required 
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Konfirmasi Kata Sandi"
        >
        <?php if (isset($errors['confirm_password'])): ?>
          <div class="text-red-500 text-sm mt-1">
            <?php foreach ($errors['confirm_password'] as $error): ?>
              <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="flex justify-between items-center pt-4">
        <a href="<?= url('admin/dashboard') ?>" class="text-gray-600 hover:text-gray-800">
          <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Kembali ke Dashboard
        </a>
        <button type="submit" class="btn btn-primary">
          Buad Admin
        </button>
      </div>
    </form>
  </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        // Validation functions
        function validateUsername() {
            if (username.value.trim() === '') {
                showError(username, 'Username is required.');
                return false;
            } else if (username.value.length > 50) {
                showError(username, 'Username must not exceed 50 characters.');
                return false;
            } else {
                clearError(username);
                return true;
            }
        }
        
        function validatePassword() {
            if (password.value.trim() === '') {
                showError(password, 'Password is required.');
                return false;
            } else if (password.value.length < 6) {
                showError(password, 'Password must be at least 6 characters.');
                return false;
            } else {
                clearError(password);
                return true;
            }
        }
        
        function validateConfirmPassword() {
            if (confirmPassword.value.trim() === '') {
                showError(confirmPassword, 'Password confirmation is required.');
                return false;
            } else if (confirmPassword.value !== password.value) {
                showError(confirmPassword, 'Password confirmation does not match.');
                return false;
            } else {
                clearError(confirmPassword);
                return true;
            }
        }
        
        // Error display functions
        function showError(element, message) {
            // Remove any existing error message
            clearError(element);
            
            // Create error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'text-red-500 text-sm mt-1 error-message';
            errorElement.textContent = message;
            
            // Insert error message after the element
            element.parentNode.insertBefore(errorElement, element.nextSibling);
            
            // Add error styling to the input
            element.classList.add('border-red-500');
        }
        
        function clearError(element) {
            // Remove error message
            const existingError = element.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Remove error styling
            element.classList.remove('border-red-500');
        }
        
        // Add event listeners for real-time validation
        username.addEventListener('blur', validateUsername);
        password.addEventListener('blur', validatePassword);
        confirmPassword.addEventListener('blur', validateConfirmPassword);
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            // Validate all fields
            const isUsernameValid = validateUsername();
            const isPasswordValid = validatePassword();
            const isConfirmPasswordValid = validateConfirmPassword();
            
            // If any validation fails, prevent form submission
            if (!isUsernameValid || !isPasswordValid || !isConfirmPasswordValid) {
                e.preventDefault();
                
                // Scroll to the first error
                const firstError = form.querySelector('.error-message');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
</script>

<?php
$title = 'Manajemen Admin | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>