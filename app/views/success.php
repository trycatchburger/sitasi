<?php
// Set success message in session
$_SESSION['success_message'] = 'Thank you, your thesis submission has been successfully sent. We will review it shortly.';

// Redirect to home page where the popup will be displayed
header('Location: ' . url());
exit;
?>
