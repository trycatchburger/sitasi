# Skripsi Submission System Documentation

## 1. Project Overview

The Skripsi Submission System is a web-based application designed to streamline the process of submitting and managing thesis proposals. Students can use the system to fill out a form with their details and upload their thesis files. Administrators can then log in to a dashboard to view, manage, and track the status of these submissions. The system also provides email notifications to both students and administrators upon successful submission or in case of an error.

## 2. Features

*   **Student Submission Form**: A user-friendly form for students to submit their thesis proposals, including personal details and file uploads.
*   **Admin Dashboard**: A secure dashboard for administrators to view and manage all submissions.
*   **Status Tracking**: Submissions can be assigned a status (e.g., Pending, Accepted, Rejected), which is visible on the admin dashboard.
*   **Email Notifications**:
    *   Students receive an email confirmation with a PDF receipt upon successful submission.
    *   Students receive an email notification if their submission fails.
    *   Administrators receive an email notification for each new submission.
*   **PDF Generation**: A PDF receipt is automatically generated for each successful submission.
*   **File Uploads**: Supports uploading multiple files with a submission.
*   **Admin Management**: Administrators can create new admin users through both the web interface and command-line scripts.
*   **File Management**: Administrators can view and download submitted files directly from the web interface.
*   **Simple Routing**: A basic routing system to handle different pages and actions.

## 3. Technologies Used

*   **Backend**: PHP
*   **Database**: MySQL/MariaDB
*   **Frontend**: HTML, Tailwind CSS
*   **Libraries**:
    *   [PHPMailer](https://github.com/PHPMailer/PHPMailer): For sending emails.
    *   [FPDF](http://www.fpdf.org/): For generating PDF receipts.
    *   [Composer](https://getcomposer.org/): For managing PHP dependencies.
    *   [npm](https://www.npmjs.com/): For managing JavaScript dependencies (specifically for Tailwind CSS).

## 4. Installation and Setup

1.  **Clone the repository**:
    ```bash
    git clone <repository-url>
    cd formskripsi
    ```

2.  **Install PHP dependencies**:
    ```bash
    composer install
    ```

3.  **Install JavaScript dependencies**:
    ```bash
    npm install
    ```

4.  **Build CSS**:
    ```bash
    npm run build
    ```

5.  **Database Setup**:
    *   Create a new database named `skripsi_db`.
    *   Import the `database.sql` file into your database. This will create the necessary tables and a default admin user.

6.  **Configuration**:
    *   Open `config.php`.
    *   Configure your SMTP server settings for sending emails.
    *   Update the `admin_email` to the email address where you want to receive submission notifications.

7.  **Web Server**:
    *   Point your web server (e.g., Apache, Nginx) to the `public` directory.
    *   Ensure that URL rewriting is enabled. An example `.htaccess` file is provided for Apache.

8.  **Default Admin Credentials**:
    *   **Username**: admin
    *   **Password**: admin123

## 5. Running the Project with XAMPP

These instructions will guide you on how to set up and run the project on your local machine using XAMPP.

**Prerequisites**:

*   **XAMPP**: Make sure you have XAMPP installed on your computer. You can download it from the [official Apache Friends website](https://www.apachefriends.org/index.html).
*   **Composer**: You need Composer to install the PHP dependencies. You can download it from the [official Composer website](https://getcomposer.org/).
*   **Node.js and npm**: You need Node.js and npm to install the JavaScript dependencies and build the CSS. You can download them from the [official Node.js website](https://nodejs.org/).

**Step-by-step instructions**:

1.  **Start XAMPP**:
    *   Open the XAMPP Control Panel.
    *   Start the **Apache** and **MySQL** services.

2.  **Place the project in `htdocs`**:
    *   Navigate to the `htdocs` directory inside your XAMPP installation folder (usually `C:\xampp\htdocs`).
    *   Place the project folder (`formskripsi`) inside the `htdocs` directory.

3.  **Create the database**:
    *   Open your web browser and go to `http://localhost/phpmyadmin`.
    *   Click on the **New** button on the left sidebar.
    *   Enter `skripsi_db` as the database name and click **Create**.
    *   Click on the newly created `skripsi_db` database.
    *   Go to the **Import** tab.
    *   Click on **Choose File** and select the `database.sql` file from the project directory.
    *   Click **Go** to import the database schema.

4.  **Install dependencies**:
    *   Open a terminal or command prompt.
    *   Navigate to the project directory:
        ```bash
        cd C:\xampp\htdocs\formskripsi
        ```
    *   Install PHP dependencies:
        ```bash
        composer install
        ```
    *   Install JavaScript dependencies:
        ```bash
        npm install
        ```

5.  **Build the CSS**:
    ```bash
    npm run build
    ```

6.  **Configure the application**:
    *   Open the `config.php` file in a text editor.
    *   Update the email configuration with your SMTP server details.

7.  **Run the application**:
    *   Open your web browser and go to `http://localhost/formskripsi/`.

**Default Admin Credentials**:

*   **Username**: admin
*   **Password**: admin123

## 6. Project Structure

```
.
├── app
│   ├── Controllers
│   │   ├── AdminController.php
│   │   ├── Controller.php
│   │   ├── FileController.php
│   │   └── SubmissionController.php
│   ├── helpers
│   │   └── url.php
│   ├── Models
│   │   ├── Admin.php
│   │   ├── Database.php
│   │   ├── EmailService.php
│   │   ├── PdfService.php
│   │   ├── Submission.php
│   │   └── ValidationService.php
│   └── views
│       ├── create_admin.php
│       ├── dashboard.php
│       ├── errors
│       │   └── 404.php
│       ├── home.php
│       ├── login.php
│       ├── main.php
│       ├── new.php
│       ├── submit_form.html
│       └── success.php
├── public
│   ├── css
│   │   ├── custom.css
│   │   └── style.css
│   └── uploads
├── src
│   └── input.css
├── .htaccess
├── AdminController.php
├── composer.json
├── config.php
├── create_admin.php
├── database.sql
├── index.php
├── list_admins.php
├── package.json
└── tailwind.config.js
```

*   **`app`**: The core application directory.
    *   **`Controllers`**: Handles user requests and interacts with models and views.
    *   **`helpers`**: Contains helper functions used throughout the application.
    *   **`Models`**: Manages the application's data and business logic, including database interactions.
    *   **`views`**: Contains the presentation layer (HTML templates).
*   **`public`**: The web server's document root. Contains publicly accessible files like CSS, JavaScript, and uploaded files.
    *   **`css`**: Directory containing the compiled CSS files.
    *   **`uploads`**: Directory where submitted files are stored.
*   **`src`**: Contains the source files for frontend assets (e.g., raw CSS).
*   **`.htaccess`**: Apache configuration for URL rewriting.
*   **`composer.json`**: Defines PHP dependencies.
*   **`config.php`**: Application configuration, including database and email settings.
*   **`create_admin.php`**: CLI script to create admin users.
*   **`database.sql`**: The database schema.
*   **`index.php`**: The single entry point for the application.
*   **`list_admins.php`**: CLI script to list all admin users.
*   **`package.json`**: Defines JavaScript dependencies.
*   **`tailwind.config.js`**: Configuration file for the Tailwind CSS framework.

## 7. Routing

The application uses a simple router in `index.php`. The URL structure is as follows:

`/controller/method/param1/param2/...`

*   **`controller`**: The name of the controller class (e.g., `admin`, `submission`).
*   **`method`**: The method to call on the controller (e.g., `login`, `new`).
*   **`params`**: Additional parameters passed to the method.

**Examples**:

*   `/`: The home page.
*   `/admin/login`: Calls the `login()` method on the `AdminController`.
*   `/admin/create`: Calls the `create()` method on the `AdminController` to display the form for creating new admin users.
*   `/admin/dashboard`: Calls the `dashboard()` method on the `AdminController`.
*   `/file/view/123`: Calls the `view(123)` method on the `FileController` to view a file directly in the browser.
*   `/file/download/123`: Calls the `download(123)` method on the `FileController` to download all files for a submission as a ZIP archive.
*   `/file/downloadAll`: Calls the `downloadAll()` method on the `FileController` to download all accepted submissions organized by year, program study, and student name.
*   `/submission/new`: Calls the `new()` method on the `SubmissionController`.
*   `/submission/view/123`: Calls the `view(123)` method on the `SubmissionController`.

## 8. Controllers

### `Controller.php`

An abstract base controller with a `render()` method to load views and pass data to them. All other controllers extend this class.

### `AdminController.php`

Handles all administrator-related actions.

*   **`login()`**: Displays the admin login page.
*   **`authenticate()`**: Processes the admin login form.
*   **`dashboard()`**: Displays the admin dashboard with a list of all submissions.
*   **`logout()`**: Logs the administrator out.
*   **`create()`**: Displays the form to create a new admin user and processes the form submission.
*   **`updateStatus()`**: Updates the status of a submission and sends email notifications to the student.

### `SubmissionController.php`

Handles the student submission process.

*   **`new()`**: Displays the new submission form.
*   **`create()`**: Processes the submission form data, creates a new submission, generates a PDF receipt, and sends email notifications.
*   **`view(int $id)`**: Displays the details of a single submission (for admins).

### `FileController.php`

Handles file viewing and downloading operations for administrators.

*   **`view(int $fileId)`**: Displays a file directly in the browser. Only accessible to logged-in administrators.
*   **`download(int $submissionId)`**: Downloads all files for a specific submission as a ZIP archive. Only accessible to logged-in administrators.
*   **`downloadAll()`**: Downloads all accepted submissions organized by year, program study, and student name. Only accessible to logged-in administrators.

## 9. Models

### `Database.php`

Implements a singleton pattern to manage the database connection.

*   **`getInstance()`**: Returns the single instance of the `Database` class.
*   **`getConnection()`**: Returns the `mysqli` database connection object.

### `Admin.php`

Hanldes database operations related to administrators.

*   **`findByUsername(string $username)`**: Finds an admin by their username.

### `Submission.php`

Hanldes database operations related to submissions.

*   **`create(array $data, array $files)`**: Creates a new submission and uploads its associated files.
*   **`findAll()`**: Retrieves all submissions from the database.
*   **`findById(int $id)`**: Finds a single submission by its ID, including its associated files.

### `EmailService.php`

Hanldes sending emails using PHPMailer.

*   **`sendSuccessNotificationToStudent(array $submissionData, string $pdfPath)`**: Sends a success notification to the student with the PDF receipt attached.
*   **`sendSuccessNotificationToAdmin(array $submissionData)`**: Sends a notification to the admin about the new submission.
*   **`sendFailureNotificationToStudent(string $email, string $name, string $errorMessage)`**: Sends a failure notification to the student.

### `PdfService.php`

Hanldes the generation of PDF receipts using FPDF.

*   **`generateReceipt(array $data)`**: Generates a PDF receipt for a submission.

### `ValidationService.php`

Provides methods for validating form data and file uploads.

*   **`validate(array $data, array $rules)`**: Validates form data based on a set of rules.
*   **`validateFiles(array $files, array $rules)`**: Validates uploaded files.

### Helper Functions

Helper functions are utility functions that can be used throughout the application.

*   **`url(string $path = '')`**: Generates a full URL with the base path. This function is defined in `app/helpers/url.php`.

## 10. Views

*   **`home.php`**: The main landing page of the application.
*   **`login.php`**: The admin login form.
*   **`dashboard.php`**: The admin dashboard, which displays a table of all submissions.
*   **`new.php`**: The form for students to submit their thesis proposals.
*   **`success.php`**: A success message displayed after a student successfully submits the form.
*   **`main.php`**: The main layout template, which includes the header, footer, and content area.
*   **`create_admin.php`**: The form for creating new admin users.
*   **`errors/404.php`**: The page displayed for 404 Not Found errors.

## 11. Database Schema

The database schema is defined in the `database.sql` file. It consists of three tables:

### `admins`

Stores the login credentials for administrators.

| Column          | Type         | Description                  |
| --------------- | ------------ | ---------------------------- |
| `id`            | `int(11)`    | Primary Key                  |
| `username`      | `varchar(50)`| The admin's username (unique)|
| `password_hash` | `varchar(255)`| The hashed password          |
| `created_at`    | `timestamp`  | The timestamp of creation    |

### `submissions`

Stores all the data for the thesis submissions.

| Column            | Type                                       | Description                               |
| ----------------- | ------------------------------------------ | ----------------------------------------- |
| `id`              | `int(11)`                                  | Primary Key                               |
| `admin_id`        | `int(11)`                                  | Foreign key to the `admins` table         |
| `nama_mahasiswa`  | `varchar(255)`                             | The student's name                        |
| `nim`             | `varchar(50)`                              | The student's ID number (unique)          |
| `email`           | `varchar(255)`                             | The student's email address               |
| `dosen1`          | `varchar(255)`                             | The first supervisor's name               |
| `dosen2`          | `varchar(255)`                             | The second supervisor's name              |
| `judul_skripsi`   | `text`                                     | The title of the thesis                   |
| `program_studi`   | `varchar(100)`                             | The student's study program               |
| `tahun_publikasi` | `year(4)`                                  | The year of publication                   |
| `status`          | `enum('Pending','Diterima','Ditolak','Digantikan')` | The status of the submission              |
| `keterangan`      | `text`                                     | Additional notes or comments              |
| `notifikasi`      | `varchar(255)`                             | Notification message                      |
| `created_at`      | `timestamp`                                | The timestamp of creation                 |
`updated_at`      | `timestamp`                                | The timestamp of the last update          |

### `submission_files`

Stores the paths to the files associated with each submission.

| Column          | Type         | Description                       |
| --------------- | ------------ | --------------------------------- |
| `id`            | `int(11)`    | Primary Key                       |
| `submission_id` | `int(11)`    | Foreign key to the `submissions` table |
| `file_path`     | `text`       | The path to the uploaded file     |
| `file_name`     | `varchar(255)`| The original name of the file     |
| `uploaded_at`   | `timestamp`  | The timestamp of the upload       |

## 12. Configuration

The `config.php` file contains the configuration for the email service.

```php
<?php

return [
    'mail' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'user@example.com',
        'password' => 'your-smtp-password',
        'from_address' => 'noreply@example.com',
        'from_name' => 'Skripsi App',
        'admin_email' => 'admin@example.com'
    ]
];
```

You need to replace the placeholder values with your actual SMTP server settings.

## 13. Standalone Scripts

The application includes several standalone PHP scripts that can be run from the command line for administrative tasks.

### `create_admin.php`

This script creates a new admin user in the database.

**Usage**:
```bash
php create_admin.php [username] [password]
```

*   **`username`**: (Optional) The username for the new admin user. Defaults to 'admin' if not provided.
*   **`password`**: (Optional) The password for the new admin user. Defaults to 'admin123' if not provided.

**Examples**:
```bash
# Create an admin user with default credentials
php create_admin.php

# Create an admin user with custom credentials
php create_admin.php myuser mypassword
```

### `list_admins.php`

This script lists all admin users in the database.

**Usage**:
```bash
php list_admins.php
```

## 14. Setting Up the Codebase on GitHub

These instructions will guide you on how to set up the project repository on GitHub.

**Step-by-step instructions**:

1.  **Create a new repository on GitHub**:
    *   Log in to your GitHub account.
    *   Click the **New** button on the top right of your repositories page, or go to https://github.com/new.
    *   Enter a repository name (e.g., `skripsi-submission-system`).
    *   Optionally, add a description for your repository.
    *   Choose if the repository should be **Public** or **Private**.
    *   Do **not** initialize the repository with a README, .gitignore, or license as we'll be pushing an existing repository.
    *   Click **Create repository**.

2.  **Push the existing codebase to GitHub**:
    *   Open a terminal or command prompt.
    *   Navigate to your project directory (if not already there):
        ```bash
        cd /path/to/formskripsi
        ```
    *   Initialize the local directory as a Git repository (if not already done):
        ```bash
        git init
        ```
    *   Add the files in your new local repository:
        ```bash
        git add .
        ```
    *   Commit the files that you've staged:
        ```bash
        git commit -m "Initial commit"
        ```
    *   Add the GitHub repository as a remote:
        ```bash
        git remote add origin https://github.com/your-username/your-repository-name.git
        ```
        Replace `your-username` with your GitHub username and `your-repository-name` with the name of the repository you created.
    *   Push the changes to GitHub:
        ```bash
        git branch -M main
        git push -u origin main
        ```

3.  **Verify the repository on GitHub**:
    *   Go to your repository page on GitHub.
    *   Confirm that all files have been uploaded correctly.
    *   You can now manage your codebase through GitHub, including creating branches, making pull requests, and collaborating with others.

**Note**: If you're using sensitive configuration files, make sure to add them to `.gitignore` to prevent them from being uploaded to GitHub. The project already includes a `.gitignore` file, but you may need to modify it based on your specific needs.
