# Database Schema Update Instructions

This repository includes an updated database schema to support new features. If you're setting up this project for the first time or your database is outdated, you'll need to run the database update script.

## What's New in the Database

The following changes have been made to support new functionality:

1. **Journal Submissions Support**:
   - Added `submission_type` column to distinguish between bachelor, master, and journal submissions
   - Added `abstract` column specifically for journal submissions

2. **Serial Number Support**:
   - Added `serial_number` column to track document numbers

3. **NIM Flexibility**:
   - Made `nim` column nullable to support journal submissions that don't require student ID

4. **Performance Improvements**:
   - Added database indexes for faster queries

## How to Update Your Database

### Method 1: Using phpMyAdmin (Recommended for beginners)

1. Open your web browser and navigate to your phpMyAdmin interface (usually `http://localhost/phpmyadmin`)
2. Select your database from the left sidebar (typically named `skripsi_db`)
3. Click on the "SQL" tab at the top
4. Copy the entire content of `update_database_schema.sql` file and paste it into the SQL query box
5. Click "Go" to execute the queries
6. You should see a success message confirming the database updates

### Method 2: Using MySQL Command Line

1. Open your command prompt or terminal
2. Navigate to the project directory
3. Run the following command (replace `your_database_name` with your actual database name):

```bash
mysql -u root -p your_database_name < update_database_schema.sql
```

You'll be prompted to enter your MySQL password.

### Method 3: Using MySQL Workbench or Other GUI Tools

1. Open your MySQL client tool
2. Connect to your database server
3. Open the `update_database_schema.sql` file
4. Execute the entire script
5. Check for any error messages in the output

## Important Notes

- **Backup First**: Before running the update, it's recommended to backup your existing database
- **Safe to Run Multiple Times**: The script includes checks to prevent errors if changes are already applied
- **Database Name**: Make sure you're updating the correct database (default is `skripsi_db`)
- **MySQL Version**: The script uses standard MySQL syntax compatible with most recent versions

## Troubleshooting

If you encounter any errors:

1. Make sure your MySQL server is running
2. Verify you have the correct database name and permissions
3. Check that you're using a compatible MySQL version
4. The script should show which specific command failed, which can help identify the issue

After successfully running the database update, your application should work correctly with all the new features.