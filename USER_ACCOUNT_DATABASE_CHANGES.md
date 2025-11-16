# User Account Database Schema Changes

## Overview
This document outlines the database schema changes implemented for the user account feature in the library system. The changes include creating a new users table and modifying the submissions table to link to user accounts.

## Changes Implemented

### 1. New Users Table
A new `users` table has been created with the following structure:

```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `library_card_number` varchar(50) NOT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `library_card_number` (`library_card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Fields Description:
- `id`: Primary key, auto-incrementing integer
- `library_card_number`: Unique identifier for user login (50 characters max)
- `name`: User's full name (255 characters max)
- `email`: User's email address (255 characters max)
- `password_hash`: Hashed password using PHP's password_hash function (255 characters max)
- `created_at`: Timestamp when the record was created
- `updated_at`: Timestamp when the record was last updated

### 2. Updated Submissions Table
The `submissions` table has been modified to include a foreign key reference to the `users` table:

```sql
ALTER TABLE `submissions` 
ADD COLUMN `user_id` int(11) NULL,
ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
```

#### New Field Description:
- `user_id`: Foreign key linking submissions to users (allows NULL values)

## Purpose of Changes

1. **User Authentication**: The `users` table allows for library card number-based authentication
2. **Submission Linking**: The `user_id` column in `submissions` links existing and new submissions to user accounts
3. **Migration Strategy**: Existing submissions can be linked to user accounts by matching user details

## Migration Strategy

When users log in for the first time:
1. The system will check for existing submissions that match the user's name and email
2. If matches are found, users will be prompted to confirm the association
3. Once confirmed, those submissions will be linked to the user account via the `user_id` column

## Security Considerations

1. **Password Hashing**: Passwords are stored as secure hashes using PHP's password_hash function
2. **Unique Library Card Numbers**: Library card numbers are enforced as unique to prevent conflicts
3. **Foreign Key Constraints**: Proper relationships ensure data integrity between users and submissions

## Files Created

1. `add_users_table.sql` - SQL script to create the users table
2. `update_submissions_table.sql` - SQL script to modify the submissions table
3. `test_user_schema_changes.php` - Test script to verify the changes

## Implementation Notes

- The `user_id` column in submissions allows NULL values to maintain backward compatibility
- Admin users can still create submissions without linking to a user account
- Foreign key constraints ensure referential integrity with ON DELETE SET NULL and ON UPDATE CASCADE
- Library card numbers serve as the unique login identifier instead of usernames