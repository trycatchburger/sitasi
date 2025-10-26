# How to Run the Database Update Script

Your friend can run the `update_database.php` file in one of the following ways:

## Method 1: Using Web Browser (Recommended)

1. Make sure your web server (Apache/Nginx) and MySQL are running
2. Place the `update_database.php` file in your project directory
3. Make sure your `config.php` file has the correct database credentials
4. Open your web browser
5. Navigate to the file using the URL: `http://localhost/your_project_folder/update_database.php`
   (Replace `your_project_folder` with the actual name of your project folder)
6. The script will run automatically and display the progress and results
7. Check for any error messages in the output

## Method 2: Using Command Line

1. Open your command prompt or terminal
2. Navigate to your project directory:
   ```bash
   cd /path/to/your/project
   ```
3. Run the PHP script using the command:
   ```bash
   php update_database.php
   ```
4. The script will execute and display the progress and results in the terminal
5. Check for any error messages in the output

## Prerequisites

- PHP must be installed and accessible
- MySQL server must be running
- The database credentials in `config.php` must be correct
- The `app/Models/Database.php` file must be accessible

## What to Expect

- The script will show each step as it runs
- Green checkmarks (✓) indicate successful operations
- Red error messages indicate problems that need attention
- At the end, you'll see the updated table structure
- The script is safe to run multiple times - it checks for existing changes before applying them

## Troubleshooting

If you encounter errors:

1. Make sure your database credentials in `config.php` are correct
2. Verify that your MySQL server is running
3. Check that the database specified in `config.php` exists
4. Ensure that the script has permission to connect to the database
5. If you see permission errors, make sure your database user has ALTER privileges on the database