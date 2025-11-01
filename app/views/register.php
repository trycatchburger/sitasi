<?php ob_start(); ?>

<div class="max-w-md mx-auto">
    <div class="card shadow-lg rounded-lg overflow-hidden">
        <div class="card-body p-8 bg-white">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4 p-3 bg-[#e7f4f0] rounded-full w-16 h-16 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#113f2d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-[#113f2d]">User Registration</h1>
                <p class="text-gray-600 mt-2">Create your account with your library card number</p>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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

            <form action="<?php echo url('user/register'); ?>" method="POST" class="space-y-6">
                <div>
                    <label for="id_member" class="block text-sm font-medium text-gray-700 mb-1">Library Card Number</label>
                    <input
                        id="id_member"
                        name="id_member"
                        type="text"
                        required
                        class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#113f2d] focus:border-[#113f2d] focus:outline-none"
                        placeholder="Enter your library card number"
                        value="<?= htmlspecialchars($_POST['id_member'] ?? '') ?>">
                    <?php if (isset($errors['id_member'])): ?>
                        <div class="text-red-500 text-sm mt-1">
                            <?php foreach ($errors['id_member'] as $error): ?>
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
                        placeholder="Create a password">
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-red-500 text-sm mt-1">
                            <?php foreach ($errors['password'] as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#13f2d] focus:border-[#113f2d] focus:outline-none"
                        placeholder="Confirm your password">
                    <?php if (isset($errors['password_confirmation'])): ?>
                        <div class="text-red-500 text-sm mt-1">
                            <?php foreach ($errors['password_confirmation'] as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-[#113f2d] hover:bg-[#0d3325] text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Register
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
        <span class="mx-2 text-gray-400">|</span>
        <a href="<?php echo url('user/login'); ?>" class="text-[#13f2d] hover:text-[#0d3325] text-sm font-medium flex items-center justify-center transition-colors">
            Already have an account? Login
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const idMember = document.getElementById('id_member');
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        
        // Validation functions
        function validateIdMember() {
            if (idMember.value.trim() === '') {
                showError(idMember, 'Library card number is required.');
                return false;
            } else if (idMember.value.length > 50) {
                showError(idMember, 'Library card number must not exceed 50 characters.');
                return false;
            } else {
                clearError(idMember);
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
        
        function validatePasswordConfirmation() {
            if (passwordConfirmation.value.trim() === '') {
                showError(passwordConfirmation, 'Password confirmation is required.');
                return false;
            } else if (password.value !== passwordConfirmation.value) {
                showError(passwordConfirmation, 'Passwords do not match.');
                return false;
            } else {
                clearError(passwordConfirmation);
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
        idMember.addEventListener('blur', validateIdMember);
        password.addEventListener('blur', validatePassword);
        passwordConfirmation.addEventListener('blur', validatePasswordConfirmation);
        passwordConfirmation.addEventListener('input', function() {
            if (password.value !== passwordConfirmation.value) {
                showError(passwordConfirmation, 'Passwords do not match.');
            } else {
                clearError(passwordConfirmation);
            }
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            // Validate all fields
            const isIdMemberValid = validateIdMember();
            const isPasswordValid = validatePassword();
            const isPasswordConfirmationValid = validatePasswordConfirmation();
            
            // If any validation fails, prevent form submission
            if (!isIdMemberValid || !isPasswordValid || !isPasswordConfirmationValid) {
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
$title = 'User Registration | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>
