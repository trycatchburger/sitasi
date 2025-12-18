# Google reCAPTCHA Configuration Guide

This document explains how to configure and use the Google reCAPTCHA feature that has been implemented in the application.

## Overview

The application now includes Google reCAPTCHA v3 to protect against spam and automated attacks on:
- Admin login form
- User login form
- Skripsi submission form
- Tesis submission form
- Journal submission form

## Configuration Steps

### 1. Get reCAPTCHA Keys from Google

1. Visit the Google reCAPTCHA admin console: https://www.google.com/recaptcha/admin
2. Register a new site with the following settings:
   - Select reCAPTCHA v2 (checkbox version) or reCAPTCHA v3
   - Add your domain (e.g., localhost for development, your domain for production)
   - Accept the terms of service
   - Click "Submit"
3. Copy the Site Key and Secret Key provided

### 2. Update Environment Variables

Edit the `.env` file in the root directory:

```
# Google reCAPTCHA Configuration
RECAPTCHA_SITE_KEY=your_recaptcha_site_key_here
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key_here
```

Replace `your_recaptcha_site_key_here` and `your_recaptcha_secret_key_here` with the actual keys obtained from Google.

### 3. Verification Process

The application automatically verifies reCAPTCHA responses on the server-side:
- When a form is submitted, the reCAPTCHA response token is sent to Google's verification API
- The server validates the response before processing the form
- If verification fails, an appropriate error message is shown to the user

## Forms Protected

The following forms now include reCAPTCHA protection:

1. **Admin Login Form** (`app/views/login.php`)
2. **User Login Form** (`app/views/user_login.php`)
3. **Main Login Popup** (`app/views/main.php`)
4. **Skripsi Submission Form** (`app/views/unggah_skripsi.php`)
5. **Tesis Submission Form** (`app/views/unggah_tesis.php`)
6. **Journal Submission Form** (`app/views/unggah_jurnal.php`)

## Implementation Details

### Frontend Changes

- Added reCAPTCHA JavaScript library to the main layout (`app/views/main.php`)
- Added reCAPTCHA widgets to all protected forms
- Widgets automatically render using the site key from the configuration

### Backend Changes

- Updated all relevant controllers to verify reCAPTCHA responses:
  - `AdminController.php` (admin login)
  - `UserController.php` (user login)
  - `SubmissionController.php` (all submission forms)
- Added configuration file at `config/recaptcha.php`
- Added environment file support for storing credentials securely

## Troubleshooting

### Common Issues

1. **reCAPTCHA not loading**: Check that the site key is correctly configured in `.env`
2. **Verification failures**: Ensure the secret key is correctly configured and the domain is registered with Google
3. **Forms not submitting**: Check browser console for JavaScript errors related to reCAPTCHA

### Testing

For testing purposes, you can temporarily bypass reCAPTCHA verification by commenting out the verification code in the controllers, but remember to re-enable it for production.

## Security Notes

- Never expose the secret key in client-side code
- Keep the `.env` file secure and out of version control
- Monitor reCAPTCHA scores if using reCAPTCHA v3 to adjust thresholds as needed