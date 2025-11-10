<!DOCTYPE html>
<html lang="en" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sitasi | Portal Unggah Skripsi Mandiri' ?></title>
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
        /* Applying the Inter font family as the default */
        body {
            font-family: 'Poppins', sans-serif;
        }

         /* Animasi lembut untuk masuk dari kiri dan kanan */
        @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-50px); }
        to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(50px); }
        to { opacity: 1; transform: translateX(0); }
        }

        .animate-fade-in-left {
        animation: fadeInLeft 1s ease-out forwards;
        }
        .animate-fade-in-right {
        animation: fadeInRight 1s ease-out forwards;
        }

        /* Efek halus hover */
        a {
        transition: all 0.3s ease;
        /* pop up menu */
        @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .animate-fadeIn {
      animation: fadeIn 0.3s ease-out;
    }
        }
    </style>
    <link href="<?php echo url('css/style.css'); ?>" rel="stylesheet">
</head>
<body class="bg-gradient-to-b from-green-50 to-white text-gray-800 antialiased">

 
<body class="font-sans text-gray-800 bg-gradient-to-b from-green-50 to-white antialiased min-h-screen flex flex-col">
    <?php
    // Check if we're on an admin page and user is logged in as admin
    $isAdminPage = isset($_SESSION['admin_id']) && (
        strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false ||
        strpos($_SERVER['REQUEST_URI'], '/admin/adminManagement') !== false ||
        strpos($_SERVER['REQUEST_URI'], '/admin/repositoryManagement') !== false ||
        strpos($_SERVER['REQUEST_URI'], '/admin/create') !== false ||
        strpos($_SERVER['REQUEST_URI'], '/admin/unpublishFromRepository') !== false ||
        strpos($_SERVER['REQUEST_URI'], '/admin/republishToRepository') !== false ||
        strpos($_SERVER['REQUEST_URI'], '/admin/deleteAdmin') !== false
    );
    ?>
<header class="sticky top-0 z-50 bg-white shadow-sm">
    <div class="container mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-2 px-4 md:px-0">

            <!-- Logo dan Judul -->
            <a href="<?php echo url(); ?>" class="flex items-center space-x-2">
                <div class="p-1 rounded-lg">
                    <img src="<?= url('public/images/logo_stainkepri.png') ?>" 
                         alt="Logo STAIN KEPRI" 
                         class="h-12 md:h-16 w-auto drop-shadow-lg">
                </div>
                <div class="flex flex-col text-green-900 font-serif leading-tight">
                    <span class="text-2xl md:text-4xl font-extrabold drop-shadow-lg uppercase" style="font-family: 'Poppins', sans-serif;">
                        SITASI
                    </span>
                    <p class="text-[9px] md:text-[10px] mt-1">
                        Sistem Unggah Tugas Akhir & Karya Ilmiah
                    </p>
                    <p class="text-[9px] md:text-[10px]">
                        Perpustakaan STAIN Sultan Abdurrahman Kepulauan Riau
                    </p>
                </div>
            </a>

            <!-- Tombol Menu Mobile -->
            <button id="menu-toggle" class="md:hidden text-green-800 focus:outline-none">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Navigasi -->
            <nav id="mobile-menu" 
                 class="hidden md:flex flex-col md:flex-row md:items-center md:space-x-6 text-gray-700 font-medium absolute md:relative top-16 md:top-0 left-0 w-full md:w-auto bg-white md:bg-transparent shadow-md md:shadow-none z-40">

                <?php if (!isset($_SESSION['admin_id'])): ?>
                    <a href="<?= url('/') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 transition">Beranda</a>
                    <a href="<?= url('submission/repository') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 transition">Repository</a>

                    <!-- Dropdown Unggah Mandiri -->
                    <div class="relative group">
                        <button class="block w-full text-left px-4 py-2 md:p-0 hover:text-green-700 flex items-center">
                            Unggah Mandiri
                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7"></path>
                            </svg>
                        </button>
                        <div class="hidden group-hover:block md:absolute left-0 mt-0 w-48 bg-white border-gray-200 rounded shadow-lg z-50">
                            <a href="<?= url('submission/skripsi') ?>" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-green-800">Unggah Skripsi</a>
                            <a href="<?= url('submission/tesis') ?>" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-green-800">Unggah Tesis</a>
                            <a href="<?= url('submission/jurnal') ?>" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-green-800">Unggah Karya Ilmiah</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="<?= url('admin/dashboard') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 <?= isActive('admin/dashboard') ? 'font-bold text-green-700' : '' ?>">Dashboard</a>
                    <a href="<?= url('admin/management_file') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 <?= isActive('admin/management_file') ? 'font-bold text-green-70' : '' ?>">Management File</a>
                    <a href="<?= url('admin/repositoryManagement') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 <?= isActive('admin/repositoryManagement') ? 'font-bold text-green-700' : '' ?>">Repository</a>
                    <a href="<?= url('admin/adminManagement') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 <?= isActive('admin/adminManagement') ? 'font-bold text-green-700' : '' ?>">Admin Management</a>
                    <a href="<?= url('admin/userManagement') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 <?= isActive('admin/userManagement') ? 'font-bold text-green-700' : '' ?>">User Management</a>
                    

                    <!-- Dropdown Admin -->
                    <div class="relative group">
                        <button class="bg-green-700 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-green-800 transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-1">
                            <a href="<?= url('admin/logout') ?>" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-800 border-b border-gray-100 last:border-b-0 transition-colors duration-150 first:rounded-t-md last:rounded-b-md">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                 <?php elseif (isset($_SESSION['user_id'])): ?>
                     <!-- Referensi Menu -->
                     <a href="<?= url('referensi') ?>" class="block px-4 py-2 md:p-0 hover:text-green-700 transition">Referensi</a>
                     
                     <!-- Dropdown User -->
                     <div class="relative group">
                         <button class="bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-blue-800 transition flex items-center">
                             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                             </svg>
                             <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                         </button>
                         <div class="hidden group-hover:block md:absolute right-0 mt-0 w-48 bg-white border border-gray-200 rounded shadow-lg z-50">
                             <a href="<?= url('user/dashboard') ?>" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-green-800">Dashboard</a>
                             <a href="<?= url('user/logout') ?>" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-green-800">
                                 <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                           d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                 </svg>
                                 Logout
                             </a>
                         </div>
                     </div>
                <?php else: ?>
                    <button id="open-login"
        class="bg-green-700 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-green-800 transition">
        Login
      </button>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<script>
    // Toggle menu mobile
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    menuToggle.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>

<!-- Success Message Popup -->
<?php if (isset($_SESSION['success_message'])): ?>
    <?php if (!empty($_SESSION['registration_success'])): ?>
        <!-- Registration Success Popup with automatic redirect -->
        <div id="registration-popup-overlay" 
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn">
            <div id="registration-success-popup" 
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8 max-w-sm w-full mx-4 relative animate-scaleUp">
                
                <!-- Icon + Title -->
                <div class="flex flex-col items-center text-center">
                    <div class="bg-green-100 p-3 rounded-full mb-3">
                        <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Registration Successful!</h3>
                    <p class="text-gray-600 text-sm mt-2"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
                </div>

                <!-- Progress bar & Timer -->
                <div class="mt-6">
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div id="redirect-progress" 
                             class="bg-green-600 h-2 rounded-full transition-all duration-1000 ease-linear" 
                             style="width: 0%">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 text-center">
                        Redirecting to login page in 
                        <span id="redirect-timer">5</span> seconds...
                    </p>
                </div>

                <!-- Action Button -->
                <div class="mt-6">
                    <button id="close-registration-popup"
                        class="w-full inline-flex justify-center rounded-xl px-4 py-2 bg-green-600 text-white font-medium hover:bg-green-700 transition focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Go to Login Now
                    </button>
                </div>
            </div>
        </div>

        <script>
        // --- Registration Success Logic ---
        document.addEventListener("DOMContentLoaded", () => {
            const progress = document.getElementById("redirect-progress");
            const timer = document.getElementById("redirect-timer");
            const btn = document.getElementById("close-registration-popup");
            let seconds = 5;

            const interval = setInterval(() => {
                seconds--;
                progress.style.width = `${(5 - seconds) * 20}%`;
                timer.textContent = seconds;

                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = "login.php";
                }
            }, 1000);

            btn.addEventListener("click", () => {
                clearInterval(interval);
                window.location.href = "login.php";
            });
        });
        </script>

        <?php unset($_SESSION['registration_success']); ?>
    <?php else: ?>
        <!-- Regular Success Popup -->
        <div id="popup-overlay" 
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 animate-fadeIn">
            <div id="success-popup" 
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8 max-w-sm w-full mx-4 relative animate-scaleUp">
                
                <div class="flex flex-col items-center text-center">
                    <div class="bg-green-100 p-3 rounded-full mb-3">
                        <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Success!</h3>
                    <p class="text-gray-600 text-sm mt-2"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
                </div>

                <div class="mt-6">
                    <button id="close-popup"
                        class="w-full inline-flex justify-center rounded-xl px-4 py-2 bg-green-600 text-white font-medium hover:bg-green-700 transition focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <script>
        // --- Regular Success Popup Logic ---
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById("close-popup").addEventListener("click", () => {
                document.getElementById("popup-overlay").classList.add("hidden");
            });
        });
        </script>
    <?php endif; ?>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<!-- Animation Styles -->
<style>
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn { animation: fadeIn 0.3s ease-out; }
.animate-scaleUp { animation: scaleUp 0.3s ease-out; }
</style>

    <!-- Error Message Popup -->
    <?php if (isset($_SESSION['error_message'])): ?>
    <div id="error-popup-overlay"
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
        <div id="error-popup"
             class="bg-white rounded-lg shadow-xl border border-gray-200 p-6 max-w-sm w-full mx-4 relative">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Error!</h3>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-sm text-gray-500"><?= htmlspecialchars($_SESSION['error_message']) ?></p>
            </div>
            <div class="mt-4">
                <button id="close-error-popup"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div> <!-- ✅ penutup overlay -->
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

    
    <main class="flex-grow w-full">
        <div class="container mx-auto px-4 my-8">
            <?= $content ?>
        </div>
    </main>
    
    <!-- Hidden CSRF Token Field -->
    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

    <?php include __DIR__ . '/footer.php'; ?>

    <script>
        // Handle popups
        document.addEventListener('DOMContentLoaded', function() {
            // Handle registration success popup (with auto redirect)
            const registrationPopup = document.getElementById('registration-success-popup');
            if (registrationPopup) {
                const registrationOverlay = document.getElementById('registration-popup-overlay');
                const closeRegistrationBtn = document.getElementById('close-registration-popup');
                const redirectTimer = document.getElementById('redirect-timer');
                const redirectProgress = document.getElementById('redirect-progress');
                
                // Timer for redirect
                let timeLeft = 5;
                let timerInterval = null;
                
                // Update the timer and progress bar
                function updateTimer() {
                    if (redirectTimer) {
                        redirectTimer.textContent = timeLeft;
                    }
                    if (redirectProgress) {
                        const progressPercent = ((5 - timeLeft) / 5) * 100;
                        redirectProgress.style.width = progressPercent + '%';
                    }
                }
                
                // Start the countdown
                timerInterval = setInterval(function() {
                    timeLeft--;
                    updateTimer();
                    
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        // Redirect to login page
                        window.location.href = '<?= url('user/login') ?>';
                    }
                }, 1000);
                
                // Update the timer initially
                updateTimer();
                
                // Close registration popup and cancel redirect when close button is clicked
                if (closeRegistrationBtn) {
                    closeRegistrationBtn.addEventListener('click', function() {
                        if (timerInterval) {
                            clearInterval(timerInterval);
                        }
                        window.location.href = '<?= url('user/login') ?>';
                    });
                }
                
                // Close popup when overlay is clicked
                if (registrationOverlay) {
                    registrationOverlay.addEventListener('click', function() {
                        if (timerInterval) {
                            clearInterval(timerInterval);
                        }
                        window.location.href = '<?= url('user/login') ?>';
                    });
                }
            }
            
            // Handle regular success popup
            const popup = document.getElementById('success-popup');
            if (popup && !popup.classList.contains('hidden')) {  // Only process if popup exists and is not hidden
                const overlay = popup.parentElement; // The overlay is the parent element
                const closeBtn = document.getElementById('close-popup');
                
                // Close popup when close button is clicked
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        if (overlay) overlay.classList.add('hidden');
                    });
                }
                
                // Close popup when overlay is clicked
                if (overlay) {
                    overlay.addEventListener('click', function(e) {
                        if (e.target === overlay) { // Only close if the overlay itself is clicked, not the popup content
                            overlay.classList.add('hidden');
                        }
                    });
                }
                
                // Auto close popup after 5 seconds
                setTimeout(function() {
                    if (popup && !popup.classList.contains('hidden')) {
                        if (overlay) overlay.classList.add('hidden');
                    }
                }, 5000);
            }
            
            // Handle error popup
            const errorPopup = document.getElementById('error-popup');
            if (errorPopup && !errorPopup.classList.contains('hidden')) { // Only process if popup exists and is not hidden
                const errorOverlay = errorPopup.parentElement; // The overlay is the parent element
                const closeErrorBtn = document.getElementById('close-error-popup');
                
                // Close popup when close button is clicked
                if (closeErrorBtn) {
                    closeErrorBtn.addEventListener('click', function() {
                        if (errorOverlay) errorOverlay.classList.add('hidden');
                    });
                }
                
                // Close popup when overlay is clicked
                if (errorOverlay) {
                    errorOverlay.addEventListener('click', function(e) {
                        if (e.target === errorOverlay) { // Only close if the overlay itself is clicked, not the popup content
                            errorOverlay.classList.add('hidden');
                        }
                    });
                }
                
                // Auto close popup after 5 seconds
                setTimeout(function() {
                    if (errorPopup && !errorPopup.classList.contains('hidden')) {
                        if (errorOverlay) errorOverlay.classList.add('hidden');
                    }
                }, 5000);
            }
        });
        
        // Handle dropdown menus
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                if (toggle && menu) {
                    let timeoutId;
                    
                    const showMenu = () => {
                        clearTimeout(timeoutId);
                        menu.classList.remove('hidden');
                    };
                    
                    const hideMenu = () => {
                        timeoutId = setTimeout(() => {
                            menu.classList.add('hidden');
                        }, 150); // Small delay before hiding
                    };
                    
                    toggle.addEventListener('click', (e) => {
                        e.preventDefault();
                        menu.classList.toggle('hidden');
                    });
                    
                    dropdown.addEventListener('mouseenter', showMenu);
                    dropdown.addEventListener('mouseleave', hideMenu);
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!dropdown.contains(e.target)) {
                            menu.classList.add('hidden');
                        }
                    });
                }
            });
        });
    </script>

    <!-- Login Popup -->
  <div id="login-popup"
    class="fixed inset-0 bg-black/40 hidden z-50 backdrop-blur-sm flex justify-center items-center">
    <div class="bg-white rounded-2xl shadow-xl w-80 sm:w-96 p-6 relative animate-fadeIn">

      <!-- Tombol Tutup -->
      <button id="close-login" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Header -->
      <div class="flex flex-col items-center mb-4">
        <img src="<?= url('public/images/logoperpus_landcape.png') ?>"  alt="Logo"
          class="h-12 w-auto mb-2">
        <h2 class="text-lg font-bold text-gray-800">Masuk ke SITASI</h2>
        <p class="text-sm text-gray-500">Pilih tipe akun Anda</p>
      </div>

      <!-- Tabs -->
      <div class="flex mb-4 border-b border-gray-200">
        <button id="tab-user"
          class="flex-1 text-center py-2 text-sm font-medium border-b-2 border-green-600 text-green-700 transition">
          User
        </button>
        <button id="tab-admin"
          class="flex-1 text-center py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-green-700 transition">
          Admin
        </button>
      </div>

      <!-- Form User -->
      <form id="form-user" action="<?= url('user/login') ?>" method="POST" class="space-y-4">
              <input type="text" name="library_card_number" placeholder="Library Card Number"
                class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none">
              <input type="password" name="password" placeholder="Password"
                class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none">
              <button type="submit"
                class="w-full bg-green-700 text-white py-2 rounded-md text-sm font-semibold hover:bg-green-80 transition">
                Login
              </button>
              <p class="text-sm text-gray-500 text-center">Belum punya akun? <a href="<?= url('user/register') ?>" class="text-green-700 font-medium">Daftar Disini</a></p>
      </form>
      

      <!-- Form Admin -->
      <form id="form-admin" action="<?= url('admin/login') ?>" method="POST" class="space-y-4 hidden">
        <input type="text" name="username" placeholder="Admin Username"
          class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none">
        <input type="password" name="password" placeholder="Password"
          class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-green-500 outline-none">
        <button type="submit"
          class="w-full bg-green-800 text-white py-2 rounded-md text-sm font-semibold hover:bg-green-900 transition">
          Login Admin
        </button>
      </form>

    </div>
  </div>

  <!-- Script -->
<script>
  const popup = document.getElementById('login-popup');
  const openBtn = document.getElementById('open-login');
  const closeBtn = document.getElementById('close-login');
  const tabUser = document.getElementById('tab-user');
  const tabAdmin = document.getElementById('tab-admin');
  const formUser = document.getElementById('form-user');
  const formAdmin = document.getElementById('form-admin');

  // Buka popup
  openBtn.addEventListener('click', () => popup.classList.remove('hidden'));

  // Tutup popup
  closeBtn.addEventListener('click', () => popup.classList.add('hidden'));
  popup.addEventListener('click', (e) => { if (e.target === popup) popup.classList.add('hidden'); });

  // Fungsi aktifkan tab
  function activateTab(activeTab, inactiveTab, showForm, hideForm) {
    activeTab.classList.add('border-green-600', 'text-green-700');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    inactiveTab.classList.add('border-transparent', 'text-gray-500');
    inactiveTab.classList.remove('border-green-600', 'text-green-700');
    showForm.classList.remove('hidden');
    hideForm.classList.add('hidden');
  }

  // Event tab
  tabUser.addEventListener('click', () => {
    activateTab(tabUser, tabAdmin, formUser, formAdmin);
  });

  tabAdmin.addEventListener('click', () => {
    activateTab(tabAdmin, tabUser, formAdmin, formUser);
  });
</script>

</body>
</html>
