<?php
require_once 'vendor/autoload.php';

// Include helper functions
require_once 'app/helpers/url.php';

use App\Controllers\AdminController;

// Mock the session and other required elements for testing
if (!isset($_SESSION)) {
    session_start();
}

// Create a mock AdminController to test the password reset functionality
class TestPasswordResetController extends AdminController {
    
    public function testResetPassword() {
        echo "Testing the resetUserPassword method...\n";
        
        // Get a user from the database to test with
        $userModel = new \App\Models\User();
        $users = $userModel->getAll();
        
        if (empty($users)) {
            echo "No users found in database to test with.\n";
            return false;
        }
        
        $testUser = $users[0];
        echo "Testing password reset for user ID: " . $testUser['id'] . "\n";
        
        // Simulate POST data for the password reset
        $_POST['user_id'] = $testUser['id'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture session messages
        $originalSession = $_SESSION;
        
        try {
            // Call the resetUserPassword method
            $this->resetUserPassword();
            
            // If we reach here, the method redirected, so we need to check session messages
            if (isset($_SESSION['success_message'])) {
                echo "Success: " . $_SESSION['success_message'] . "\n";
                return true;
            } elseif (isset($_SESSION['error_message'])) {
                echo "Error: " . $_SESSION['error_message'] . "\n";
                return false;
            } else {
                echo "Unexpected: No session message set.\n";
                return false;
            }
        } catch (Exception $e) {
            echo "Exception occurred: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    // Override the middleware methods to bypass authentication for testing
    protected function runMiddleware(array $middlewareNames, array $params = []): bool {
        // Do nothing for testing purposes
        return true;
    }
    
    protected function isAdminLoggedIn(): bool {
        return true; // Simulate logged in admin for testing
    }
    
    protected function generateCsrfToken(): string {
        return 'test_token';
    }
}

echo "Starting password reset functionality test...\n";

$controller = new TestPasswordResetController();
$result = $controller->testResetPassword();

if ($result) {
    echo "\nPassword reset functionality test PASSED!\n";
    echo "The 'Database error occurred while resetting user password' error should now be fixed.\n";
} else {
    echo "\nPassword reset functionality test FAILED!\n";
}

echo "Test completed.\n";