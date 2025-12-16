# Database Update Tutorial for cPanel Deployment

This tutorial explains how to update your database schema on cPanel to match the local development environment without losing existing data.

## Overview

This update fixes the login authentication issue where users with string ID members (like "KTA001") could not log in due to a schema mismatch between tables.

### The Problem
- `anggota.id_member` was `int(11)` 
- `users_login.id_member` was `varchar(50)`
- This prevented string ID members like "KTA001" from being stored consistently in both tables
- Users with string IDs could not authenticate successfully

### The Solution
- Change `anggota.id_member` from `int(11)` to `varchar(50)` to match `users_login.id_member`
- This allows both numeric and string ID members to be stored properly
- Preserves all existing data while fixing the authentication issue

## Step-by-Step Instructions

### Step 1: Backup Your Database
Before making any changes, create a backup of your database:

1. Log in to your cPanel account
2. Go to the "Databases" section
3. Click on "phpMyAdmin"
4. Select your database from the left panel
5. Click the "Export" tab at the top
6. Choose "Quick" export method
7. Select "SQL" format
8. Click "Go" to download the backup file

### Step 2: Apply Database Schema Changes

#### Method 1: Using phpMyAdmin (Recommended)
1. Log in to your cPanel account
2. Go to the "Databases" section
3. Click on "phpMyAdmin"
4. Select your database from the left panel
5. Click on the "SQL" tab at the top
6. Copy and paste the following SQL commands:

```sql
-- Modify the id_member column in anggota table to be VARCHAR(50) to match users_login
ALTER TABLE anggota MODIFY COLUMN id_member VARCHAR(50);

-- Optional: Add index for better performance on id_member column
ALTER TABLE anggota ADD INDEX idx_id_member (id_member);

-- Optional: Add index for better performance on users_login id_member column if not already present
ALTER TABLE users_login ADD INDEX idx_users_login_id_member (id_member);
```

7. Click "Go" to execute the commands

#### Method 2: Using the SQL File
1. Download the `update_database_schema.sql` file provided with this tutorial
2. Log in to your cPanel account
3. Go to the "Databases" section
4. Click on "phpMyAdmin"
5. Select your database from the left panel
6. Click on the "Import" tab at the top
7. Click "Choose File" and select the `update_database_schema.sql` file
8. Make sure "SQL" is selected as the format
9. Click "Go" to import the file

### Step 3: Verify the Changes

After applying the changes, verify that the schema has been updated:

1. In phpMyAdmin, click on your database
2. Click on the "Structure" tab for the `anggota` table
3. Verify that the `id_member` column is now `varchar(50)` instead of `int(11)`
4. Check that the `users_login` table's `id_member` column is also `varchar(50)`

### Step 4: Test the Application

1. Access your application through the web browser
2. Try logging in with a user that has a string ID member (like "KTA001")
3. Verify that the login process works correctly
4. Test with various ID member formats to ensure all work properly

## Important Notes

- **No data will be lost** during this process - the ALTER command preserves existing data
- The change from `int(11)` to `varchar(50)` is safe and allows for both numeric and string ID members
- Existing numeric ID members will continue to work as before
- New string ID members (like "KTA001") will now work correctly
- The change only affects the column definition, not the actual data values

## Troubleshooting

If you encounter any issues:

1. **Error during ALTER command**: Check that you have the necessary privileges to modify the table structure
2. **Login still not working**: Verify that both tables have the same ID member values and that the user exists in both tables
3. **Performance issues**: The optional indexes added in the script should improve query performance

## Rollback Plan

If you need to revert the changes (not recommended):

```sql
-- Revert the column type back to int (only if absolutely necessary)
ALTER TABLE anggota MODIFY COLUMN id_member INT(11);
```

**Warning**: This will cause issues if you have string ID members in your data.

## Support

If you encounter any issues during the update process, please contact your hosting provider's support team or refer to the application documentation for further assistance.