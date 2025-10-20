# Admin Dashboard Header Navigation Implementation Plan

## Overview
This document outlines the detailed implementation plan for moving the admin navigation menu from a sidebar to the header with the following menu items:
1. Admin Dashboard
2. Admin Management
3. Repository

## Current Structure Analysis
- The application uses a template system with `main.php` as the main layout
- Individual pages like `dashboard.php` are rendered within this layout
- Navigation is now handled through header links
- Admin functions are implemented in `AdminController.php`
- There is a `create_admin.php` view but no admin management/list view
- Admin data is handled by `AdminRepository.php`

## Implementation Steps

### 1. Modify Main Layout
File: `app/views/main.php`
- Update to include the admin navigation in the header for admin pages
- Add logic to determine when to show the admin navigation (when user is admin)
- Remove the sidebar include that was previously used
- Adjust the main content area to work without the sidebar layout

### 2. Update Dashboard Page
File: `app/views/dashboard.php`
- Modify layout to work without sidebar
- Ensure proper spacing and responsiveness

### 3. Update Repository Management Page
File: `app/views/repository_management.php`
- Modify layout to work without sidebar
- Ensure proper spacing and responsiveness

### 4. Test Navigation
- Test all header menu items
- Verify proper highlighting of active page
- Test responsive behavior on different screen sizes
- Verify all functionality works as expected

## Detailed File Changes

### Files Modified:
1. `app/views/main.php` - Move admin navigation to header, remove sidebar include
2. `app/views/dashboard.php` - Adjust layout for new navigation
3. `app/views/repository_management.php` - Adjust layout for new navigation

### Files Removed:
1. `app/views/components/admin_sidebar.php` - Sidebar component (no longer needed)

## Code Implementation Details

### 1. Header Navigation in Main Layout
```php
<!-- In app/views/main.php -->
<?php if (isset($_SESSION['admin_id'])): ?>
    <!-- Admin Menu Items -->
    <a href="<?= url('admin/dashboard') ?>" class="hover:text-green-700 transition duration-200 <?= isActive('admin/dashboard') ? 'font-bold text-green-700' : '' ?>">Dashboard</a>
    <a href="<?= url('admin/adminManagement') ?>" class="hover:text-green-700 transition duration-200 <?= isActive('admin/adminManagement') ? 'font-bold text-green-700' : '' ?>">Admin Management</a>
    <a href="<?= url('admin/repositoryManagement') ?>" class="hover:text-green-700 transition duration-200 <?= isActive('admin/repositoryManagement') ? 'font-bold text-green-700' : '' ?>">Repository</a>
    
    <div class="relative dropdown">
        <button class="ml-4 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 transition font-semibold shadow-sm text-sm flex items-center focus:outline-none dropdown-toggle">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7"></path>
            </svg>
        </button>
        <div class="absolute right-0 mt-0 w-48 bg-white border border-gray-200 rounded shadow-lg hidden dropdown-menu transition duration-200 z-10">
            <a href="<?= url('admin/logout') ?>" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-green-800 transition">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </a>
        </div>
    </div>
<?php else: ?>
    <a href="<?= url('admin/login') ?>" class="ml-4 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 transition font-semibold shadow-sm text-sm">
        Admin Login
    </a>
<?php endif; ?>
```

## CSS Styling Plan

### Desktop Layout:
- Header navigation: Horizontal layout with menu items
- Main content: Full width
- No sidebar needed

### Mobile Layout:
- Header navigation: Responsive with dropdown for admin menu
- Vertical layout when visible

### Active State:
- Highlight current page in header navigation
- Visual indicator for active menu item

## Testing Plan

1. Verify header navigation appears only on admin pages
2. Test all navigation links work correctly
3. Verify active page is highlighted in header
4. Test responsive behavior on mobile devices
5. Verify all existing functionality still works

## Security Considerations

1. Ensure header navigation only appears for authenticated admin users
2. Verify all admin management functions require authentication
3. Implement proper CSRF protection for admin operations
4. Add authorization checks for sensitive operations