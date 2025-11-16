# Fix Foreign Key Constraint Issue

This guide will help you fix the foreign key constraint error when adding references.

## Problem
Error: `Gagal memperbarui referensi: Failed to add submission to references: Cannot add or update a child row: a foreign key constraint fails (\`skripsi_db\`.\`user_references\`, CONSTRAINT \`user_references_ibfk_1\` FOREIGN KEY (\`user_id\`) REFERENCES \`users\` (\`id\`) ON DELETE CASCADE ON UPDATE CASCADE)`

## Solution Steps

### Step 1: Ensure XAMPP/WAMP is Running
1. Start XAMPP Control Panel
2. Start Apache and MySQL services

### Step 2: Run the Fix Script
You have two options:

#### Option A: Command Line (Recommended)
1. Open Command Prompt or Terminal
2. Navigate to your project directory
3. Run the command:
   ```bash
   php fix_foreign_key_issue.php
   ```

#### Option B: Browser Access
1. Place the `fix_foreign_key_issue.php` file in your web server directory (htdocs for XAMPP)
2. Access the file via browser: `http://localhost/fix_foreign_key_issue.php`

### Step 3: Verify the Fix
After running the script, try adding a reference again to confirm the issue is resolved.

## What the Script Does
- Checks and removes old foreign key constraints pointing to non-existent `users` table
- Creates proper foreign key constraints pointing to `users_login` table
- Ensures all necessary indexes exist
- Verifies that required tables exist

## Troubleshooting
If you get a database connection error:
1. Make sure MySQL is running in XAMPP
2. Verify your database name is `skripsi_db`
3. Check that your MySQL user is `root` with no password (default XAMPP setup)

## Files Created
- `fix_foreign_key_issue.php` - The fix script to resolve the issue