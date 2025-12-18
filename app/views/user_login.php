<?php ob_start(); ?>

<div class="max-w-md mx-auto">
    <div class="card shadow-lg rounded-lg overflow-hidden">
        <div class="card-body p-8 bg-white">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4 p-3 bg-[#e7f4f0] rounded-full w-16 h-16 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#113f2d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 0-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-[#113f2d]">User Login</h1>
                <p class="text-gray-600 mt-2">Login with your library card number</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 text-red-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && is_array($errors)): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 text-red-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 018 0z" />
                        </svg>
                        <div>
                            <strong>Validation Errors:</strong>
                            <ul class="list-disc pl-5 mt-1">
                                <?php foreach ($errors as $fieldErrors): ?>
                                    <?php foreach ($fieldErrors as $fieldError): ?>
                                        <li><?= htmlspecialchars($fieldError) ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo url('user/login'); ?>" method="POST" class="space-y-6">
                <div>
                    <label for="library_card_number" class="block text-sm font-medium text-gray-700 mb-1">Library Card Number</label>
                    <input
                        id="library_card_number"
                        name="library_card_number"
                        type="text"
                        required
                        class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#113f2d] focus:border-[#113f2d] focus:outline-none"
                        placeholder="Enter your library card number">
                    <?php if (isset($errors['library_card_number'])): ?>
                        <div class="text-red-500 text-sm mt-1">
                            <?php foreach ($errors['library_card_number'] as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#113f2d] focus:border-[#113f2d] focus:outline-none"
                        placeholder="Enter your password">
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-red-500 text-sm mt-1">
                            <?php foreach ($errors['password'] as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pt-4">
                    <div class="g-recaptcha" data-sitekey="<?php
                    $config_path = dirname(__DIR__, 2) . '/config/recaptcha.php'; // Go up 2 levels from views directory
                    if (file_exists($config_path)) {
                        $config = include $config_path;
                        if (is_array($config) && isset($config['site_key'])) {
                            echo htmlspecialchars($config['site_key']);
                        } else {
                            echo 'your_site_key_here';
                        }
                    } else {
                        echo 'your_site_key_here';
                    }
                    ?>"></div>
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-[#113f2d] hover:bg-[#0d3325] text-white font-semibold py-2 px-4 rounded-md transition duration-200 mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 16l-4-4m0 0l4-4m-4 4h14" />
                        </svg>
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 text-center py-4">
        <a href="<?php echo url(); ?>" class="text-[#113f2d] hover:text-[#0d3325] text-sm font-medium flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Home
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const libraryCardNumber = document.getElementById('library_card_number');
        const password = document.getElementById('password');
        
        // Validation functions
        function validateLibraryCardNumber() {
            if (libraryCardNumber.value.trim() === '') {
                showError(libraryCardNumber, 'Library card number is required.');
                return false;
            } else if (libraryCardNumber.value.length > 50) {
                showError(libraryCardNumber, 'Library card number must not exceed 50 characters.');
                return false;
            } else {
                clearError(libraryCardNumber);
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
        libraryCardNumber.addEventListener('blur', validateLibraryCardNumber);
        password.addEventListener('blur', validatePassword);
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            // Validate all fields
            const isLibraryCardValid = validateLibraryCardNumber();
            const isPasswordValid = validatePassword();
            
            // If any validation fails, prevent form submission
            if (!isLibraryCardValid || !isPasswordValid) {
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
$title = 'User Login | Library System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>