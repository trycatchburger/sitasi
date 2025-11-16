# How to Deploy the Skripsi Submission System to cPanel via Git

This tutorial provides a step-by-step guide on how to deploy the Skripsi Submission System to a cPanel hosting environment using Git. This process will help you maintain version control of your application while ensuring a smooth deployment to your hosting provider.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Setting Up Git on cPanel](#setting-up-git-on-cpanel)
3. [Deploying the Codebase](#deploying-the-codebase)
4. [Installing Dependencies](#installing-dependencies)
5. [Setting Up the Database](#setting-up-the-database)
6. [Configuring the Web Server](#configuring-the-web-server)
7. [Configuring the Application](#configuring-the-application)
8. [Building the CSS](#building-the-css)
9. [Testing the Deployment](#testing-the-deployment)
10. [Troubleshooting Common Issues](#troubleshooting-common-issues)

## Prerequisites

Before you begin, ensure you have the following:

1. **cPanel Account**: A hosting account with cPanel that supports Git version control
2. **SSH Access**: Enabled SSH access to your cPanel account (if possible)
3. **Database Access**: Access to create and manage a MySQL database
4. **Git Repository**: Your codebase should be in a Git repository (GitHub, GitLab, etc.)
5. **Domain Name**: A domain or subdomain where you want to deploy the application

## Setting Up Git on cPanel

### Step 1: Access cPanel Git Version Control

1. Log in to your cPanel account
2. Scroll down to the "Files" section
3. Click on "Git Version Control"

### Step 2: Create a New Git Repository

1. Click on "Create" to set up a new repository
2. Fill in the following details:
   - **Repository URL**: The URL of your Git repository (e.g., `https://github.com/yourusername/your-repo.git`)
   - **Repository Path**: The directory where you want to clone the repository (e.g., `skripsi-app`)
   - **Branch**: Usually `main` or `master`
3. Click "Create"

### Step 3: Clone the Repository

1. After creating the repository, cPanel will automatically clone it
2. Wait for the cloning process to complete
3. You should see your repository listed in the Git Version Control interface

## Deploying the Codebase

### Step 1: Update Your Repository

1. In the Git Version Control interface, find your repository
2. Click on "Manage" next to your repository
3. Click on "Pull or Deploy"
4. Click "Update from Remote" to pull the latest changes from your repository

### Step 2: Set Up the Deployment Path

1. In the "Deploy" section, set the deployment path to your domain's document root
2. For example, if your domain is `example.com`, the path might be `/home/username/public_html`
3. If you want to deploy to a subdirectory, adjust the path accordingly (e.g., `/home/username/public_html/skripsi`)

### Step 3: Deploy the Application

1. Click "Deploy" to copy the files from the repository to your deployment path
2. Wait for the deployment process to complete

## Installing Dependencies

The Skripsi Submission System uses both PHP (Composer) and JavaScript (npm) dependencies. You'll need to install these on your cPanel server.

### Installing PHP Dependencies with Composer

1. Access your cPanel File Manager or use SSH if available
2. Navigate to your application directory
3. If Composer is not installed on your cPanel account:
   - Download the Composer installer: `curl -sS https://getcomposer.org/installer | php`
   - Run Composer: `php composer.phar install --no-dev`
4. If Composer is already available:
   - Run: `composer install --no-dev`

**Note**: The `--no-dev` flag excludes development dependencies which are not needed in production.

### Installing JavaScript Dependencies with npm

1. If Node.js/npm is available on your cPanel (not all hosts provide this):
   - Navigate to your application directory
   - Run: `npm install --production`
2. If Node.js/npm is not available on cPanel:
   - Build the CSS locally and upload the compiled files
   - Or contact your hosting provider to see if they can enable Node.js

## Setting Up a Subdomain (Optional)

If you want to run the Skripsi Submission System on a subdomain rather than the main domain, you'll need to set up a subdomain in cPanel.

### Step 1: Create a Subdomain

1. In cPanel, go to the "Domains" section
2. Click on "Subdomains"
3. In the "Subdomain" field, enter the name you want for your subdomain (e.g., `skripsi`, `app`, `thesis`)
4. In the "Domain" dropdown, select your main domain
5. In the "Document Root" field, enter the directory where you want to host the application:
   - For example: `/home/username/public_html/skripsi` (where `username` is your cPanel username)
   - This should match the deployment path you set in the Git deployment settings
6. Click "Create"

### Step 2: Verify Subdomain Creation

1. After creation, you should see your subdomain listed in the Subdomains interface
2. You can click "Manage" to see more details about your subdomain
3. Test that the subdomain is working by visiting `http://your-subdomain.your-domain.com`
   - You should see the default cPanel placeholder page at this point

### Step 3: Deploy to the Subdomain

When deploying via Git, make sure to set the deployment path to match your subdomain's document root:
1. In the Git Version Control interface, find your repository
2. Click "Manage"
3. In the "Deployment Path" field, enter the same path you used for the subdomain document root
4. Click "Deploy" to copy files to your subdomain

## Setting Up a Subdirectory (Alternative to Subdomain)

If you prefer to run the Skripsi Submission System in a subdirectory of your main domain (e.g., `yourdomain.com/sitasi`) instead of a subdomain, follow these steps:

### Step 1: Create the Subdirectory

1. In cPanel, go to the "Files" section
2. Click on "File Manager"
3. Navigate to your `public_html` directory
4. Create a new directory for your application (e.g., `sitasi`)
5. This will be the base directory for your application

### Step 2: Deploy to the Subdirectory

When deploying via Git, set the deployment path to your subdirectory:
1. In the Git Version Control interface, find your repository
2. Click "Manage"
3. In the "Deployment Path" field, enter the path to your subdirectory:
   - For example: `/home/username/public_html/sitasi`
4. Click "Deploy" to copy files to your subdirectory

### Step 3: Configure the Application for Subdirectory

You need to update the `base_path` configuration in `config.php` to match your subdirectory and add database configuration as well. Also, make sure to update the database configuration for cPanel deployment. The configuration should include database connection settings specific to your cPanel hosting environment. Here's an updated example that includes both the mail configuration and the database configuration for cPanel deployment, with the base_path set to '/sitasi' to match your subdirectory name:

```php
<?php
return [
    'mail' => [
        'host' => 'your-smtp-server.com',
        'port' => 587,
        'username' => 'your-email@example.com',
        'password' => 'your-email-password',
        'from_address' => 'noreply@yourdomain.com',
        'from_name' => 'Skripsi App',
        'admin_email' => 'admin@yourdomain.com'
    ],
    'db' => [
        'host' => 'localhost',  // Usually localhost in cPanel
        'dbname' => 'your_database_name',  // Replace with your actual database name
        'username' => 'your_db_username',  // Replace with your database username
        'password' => 'your_db_password',  // Replace with your database password
        'charset' => 'utf8mb4'
    ],
    'base_path' => '/sitasi' // Set this to match your subdirectory name
];
?>
```

### Step 4: Update .htaccess for Subdirectory

The application's URL rewriting needs to be adjusted for subdirectory deployment. Update the `.htaccess` file in your application's root directory:

1. In cPanel File Manager, navigate to your subdirectory
2. Edit the `.htaccess` file in the root of your application (not in the `public` directory)
3. Update the RewriteBase directive to match your subdirectory (change /sitasi/ to match your subdirectory name):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /sitasi/  # Change this to match your subdirectory name

    # Redirect to public directory
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Also update the `.htaccess` file in the `public` directory (change /sitasi/public/ to match your subdirectory name):

1. Navigate to the `public` directory
2. Edit the `.htaccess` file
3. Update the RewriteBase directive (change /sitasi/public/ to match your subdirectory name):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /sitasi/public/  # Change this to match your subdirectory name

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php [L]
</IfModule>
```

### Step 5: Database Configuration for cPanel

When deploying to cPanel, you need to update the database configuration to match your cPanel database settings. The database configuration should match your cPanel MySQL database settings that you created during the database setup step. Make sure to replace 'your_database_name', 'your_db_username', and 'your_db_password' with the actual values from your cPanel database setup.

## Setting Up the Database

### Step 1: Create a Database

1. In cPanel, go to the "Databases" sections
2. Click on "MySQL Database Wizard"
3. Create a new database (e.g., `skripsi_db`)
4. Create a new database user and assign a strong password
5. Add the user to the database with all privileges

### Step 2: Import the Database Schema

1. In cPanel, go to "phpMyAdmin"
2. Select your newly created database
3. Click on the "Import" tab
4. Choose the `database.sql` file from your application
5. Click "Go" to import the schema

### Step 3: Create an Admin User (Optional)

You can create an admin user either through the web interface after deployment or by running a script:

1. Through the web interface:
   - Access your deployed application
   - Navigate to the admin creation page
2. Through command line (if SSH is available):
   - Navigate to your application directory
   - Run: `php create_admin.php [username] [password]`

## Configuring the Web Server

### Setting the Document Root

If you're using a subdomain, the document root should already be set correctly from the subdomain setup process. If you're using the main domain or need to adjust the document root:

1. In cPanel, go to "Domains"
2. Click on "Subdomains" or "Addon Domains" depending on your setup
3. Edit your domain settings
4. Set the document root to point to the `public` directory:
   - For example: `/home/username/public_html/skripsi/public`
   - If using a subdomain: `/home/username/public_html/subdomain-name/public`
   - If using a subdirectory: `/home/username/public_html/subdirectory-name/public`

### URL Rewriting

If you've set up the application in a subdirectory, make sure you've updated the `.htaccess` files as described in the "Setting Up a Subdirectory" section above. The URL rewriting rules need to be adjusted to account for the subdirectory path.

The Skripsi Submission System uses a public directory structure for security. You need to set the document root to the `public` directory:

1. In cPanel, go to "Domains"
2. Click on "Subdomains" or "Addon Domains" depending on your setup
3. Edit your domain settings
4. Set the document root to point to the `public` directory:
   - For example: `/home/username/public_html/skripsi/public`

### URL Rewriting

The application uses URL rewriting for clean URLs. The project includes an `.htaccess` file that should work with Apache servers:

1. Ensure that `mod_rewrite` is enabled on your server (most cPanel installations have this by default)
2. The `.htaccess` file in the `public` directory should handle URL rewriting automatically

## Configuring the Application

### Step 1: Update Configuration File

1. Locate the `config.php` file in your application root
2. Update the configuration with your specific settings:

```php
<?php
return [
    'mail' => [
        'host' => 'your-smtp-server.com',
        'port' => 587,
        'username' => 'your-email@example.com',
        'password' => 'your-email-password',
        'from_address' => 'noreply@yourdomain.com',
        'from_name' => 'Skripsi App',
        'admin_email' => 'admin@yourdomain.com'
    ],
    'base_path' => '/your-base-path' // Only needed if not in root
];
?>
```

**Note about base_path configuration:**
- If you're deploying to a subdomain (e.g., `skripsi.yourdomain.com`), you typically don't need to set a base path as the subdomain acts as the root
- If you're deploying to a subdirectory of your main domain (e.g., `yourdomain.com/skripsi`), set the base path to `/skripsi`
- If you're deploying to the root of your main domain (e.g., `yourdomain.com`), you can leave this empty or comment it out

### Step 2: Set File Permissions

Ensure that the following directories have write permissions:

1. `public/uploads` - For storing uploaded files
2. `cache` - For caching (if used)

To set permissions:
1. In cPanel File Manager, right-click on the directory
2. Select "Change Permissions"
3. Set permissions to 755 for directories and 644 for files
4. For the `public/uploads` directory, you might need 775 permissions

## Building the CSS

The Skripsi Submission System uses Tailwind CSS. If you couldn't build the CSS on the server:

1. Build the CSS locally:
   - Run `npm run build` in your local development environment
2. Upload the compiled CSS file (`public/css/style.css`) to your server
3. Ensure the file is in the correct location: `public/css/style.css`

## Testing the Deployment

### Step 1: Access the Application

1. Visit your domain in a web browser
2. You should see the home page of the Skripsi Submission System

### Step 2: Test Admin Functionality

1. Navigate to the admin login page (usually `/admin/login`)
2. Use the default credentials:
   - Username: `admin`
   - Password: `admin123`
3. Change the default password after first login

### Step 3: Test Form Submission

1. Navigate to the submission form (usually the home page)
2. Fill out the form with test data
3. Upload test files
4. Submit the form
5. Check if:
   - The submission is recorded in the database
   - Email notifications are sent
   - Files are uploaded correctly

## Troubleshooting Common Issues

### 1. "Deploy Head Commit" Option Greyed Out

If the "Deploy Head Commit" option is greyed out in cPanel Git Version Control:

- **Check Repository Connection**: Ensure your repository was properly cloned. Try removing and re-adding the repository.
- **Verify Branch Selection**: Make sure you have selected a valid branch. The default is usually `main` or `master`.
- **Check for Commits**: Ensure your repository has at least one commit. An empty repository will not have a head commit to deploy.
- **Permissions Issue**: Verify that cPanel has proper read permissions for your repository.
- **Repository Status**: Check if there are any errors in the repository status section of cPanel Git Version Control.

To resolve this issue:
1. In cPanel Git Version Control, delete the existing repository entry
2. Wait a few moments for cleanup
3. Re-add the repository with the correct URL and branch
4. Wait for the cloning process to complete
5. The "Deploy Head Commit" option should now be enabled

### 2. "500 Internal Server Error"

- Check file permissions (should be 644 for files, 755 for directories)
- Verify that all required PHP extensions are installed
- Check the error logs in cPanel for more details

### 3. "404 Not Found" for Pages

- Ensure the document root is set to the `public` directory
- Verify that `.htaccess` files are present and readable
- Check if `mod_rewrite` is enabled

### 4. Database Connection Issues

- Verify database credentials in your configuration
- Ensure the database user has proper permissions
- Check if the database server is accessible

### 5. Email Not Sending

- Verify SMTP settings in `config.php`
- Check if your hosting provider allows SMTP connections
- Ensure your email credentials are correct

### 6. File Upload Issues

- Check permissions on the `public/uploads` directory
- Verify that the directory exists and is writable
- Check PHP upload limits in your hosting configuration

### 7. Composer Dependencies Not Found

- Ensure Composer installed dependencies successfully
- Check that the `vendor` directory exists and contains files
- Verify that the autoloader is working correctly

## Updating the Application

To update your application after making changes to your Git repository:

1. In cPanel Git Version Control, find your repository
2. Click "Manage"
3. Click "Pull or Deploy"
4. Click "Update from Remote" to fetch the latest changes
5. Click "Deploy" to update your live application

## Setting Up Automatic Updates

cPanel's Git Version Control offers features to automatically deploy your application when changes are pushed to your repository.

### Method 1: Using cPanel's Automatic Deployment

1. In cPanel Git Version Control, find your repository
2. Click "Manage"
3. In the "Deploy" section, look for "Automatic Deployment" settings
4. Enable "Automatically update the deployment directory when a new commit is received"
5. Ensure the correct deployment path is set
6. Save the settings

**Note**: If the "Deploy Head Commit" option is greyed out, refer to the troubleshooting section above. Automatic deployment requires this feature to be working properly.

With this option enabled, any time you push changes to your tracked branch, cPanel will automatically pull the changes and deploy them to your website.

### Method 2: Using .cpanel.yml for Advanced Deployment

For more control over the deployment process, you can create a `.cpanel.yml` file in the root of your repository. This file allows you to define custom deployment tasks, such as installing dependencies and setting file permissions.

An example `.cpanel.yml` file has been included in this repository. To use it:

1. Ensure the `.cpanel.yml` file is in the root of your repository
2. Update the paths in the file to match your cPanel username and desired deployment path
3. When cPanel deploys your application, it will automatically execute the tasks defined in this file

The provided `.cpanel.yml` file includes:
- Copying files to the correct deployment directory
- Installing PHP dependencies with Composer
- Setting appropriate file permissions
- Environment configuration

For subdirectory deployments, make sure to:
- Update the `path` variable in the `.cpanel.yml` file to match your subdirectory path
- Set the `base_path` in `config.php` to match your subdirectory (e.g., `/skripsi`)
- Review the notes section at the end of the `.cpanel.yml` file for additional considerations

### Method 3: Setting Up a Post-Receive Hook (Advanced)

If your cPanel installation supports Git hooks, you can set up automatic deployment using a post-receive hook:

1. Access your repository directory in cPanel File Manager or via SSH
2. Navigate to the `.git/hooks` directory
3. Create a new file named `post-receive` (no extension)
4. Add the following content to the file:

```bash
#!/bin/bash
cd /home/username/public_html/your-app-directory
git --git-dir=/home/username/repositories/your-repo.git --work-tree=/home/username/public_html/your-app-directory checkout -f
```

5. Make the hook executable:
   - In SSH: `chmod +x .git/hooks/post-receive`
   - In File Manager: Right-click the file, select "Change Permissions", and set to 755

### Method 4: Using Cron Jobs for Periodic Updates

If automatic deployment isn't available, you can set up a cron job to periodically pull updates:

1. In cPanel, go to "Advanced" section
2. Click on "Cron Jobs"
3. Create a new cron job with the following settings:
   - Common Settings: "Once Per Hour" or your preferred frequency
   - Command: `cd /home/username/public_html/your-app-directory && /usr/local/bin/git pull origin main`
4. Click "Add New Cron Job"

**Note**: Adjust the paths and branch name according to your setup.

## Security Considerations

1. **Change Default Credentials**: Immediately change the default admin password
2. **Secure Configuration Files**: Ensure `config.php` is not accessible via web
3. **File Permissions**: Use appropriate file permissions to prevent unauthorized access
4. **Regular Updates**: Keep your dependencies updated
5. **Backup**: Regularly backup your database and files

## Conclusion

You've successfully deployed the Skripsi Submission System to cPanel using Git. This setup allows you to maintain version control while having a reliable deployment process. You can deploy the application either to a subdomain, a subdirectory, or the root of your main domain, depending on your preferences.

You can also set up automatic updates to ensure your application stays up-to-date with the latest changes from your repository. For more advanced deployment automation, consider using the included `.cpanel.yml` file which can handle tasks like dependency installation and file permissions automatically.

Remember to regularly update your application and monitor for any issues. If you encounter any problems during the deployment process, consult the troubleshooting section or contact your hosting provider for assistance with server-specific configurations.