<?php http_response_code(422); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>422 Unprocessable Entity</title>
    <link rel="icon" type="image/png" href="../../public/images/icons/favicon.png">
    <link href="../../css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-8">
    <div class="max-w-md w-full mx-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mx-auto mb-4 p-3 bg-red-50 rounded-full w-16 h-16 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.7-1.3-2.694-1.333-3.464 0L3.34 16c-.7 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">422</h1>
                <p class="text-xl text-gray-600 mb-1"><?php echo htmlspecialchars($GLOBALS['errorMessage'] ?? 'Validation Error'); ?></p>
                <div><?php echo $GLOBALS['errorDetails'] ?? ''; ?></div>
                <p class="text-gray-500 mt-2 mb-6">The data you submitted is invalid.</p>
                <a href="javascript:history.back()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Go Back
                </a>
            </div>
        </div>
    </div>
</body>
</html>