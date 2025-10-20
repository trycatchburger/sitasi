<?php

require_once __DIR__ . '/app/Controllers/Controller.php';
require_once __DIR__ . '/app/models/Admin.php';
require_once __DIR__ . '/app/models/Submission.php';

$config = require_once __DIR__ . '/config.php';
$basePath = $config['base_path'] ?? '';

class AdminController extends Controller {

    private function isLoggedIn(): bool {
        return isset($_SESSION['admin_id']);
    }

    public function login() {
        if ($this->isLoggedIn()) {
            header('Location: ' . url('admin/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminModel = new Admin();
            $admin = $adminModel->findByUsername($_POST['username']);

            if ($admin && password_verify($_POST['password'], $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                header('Location: ' . url('admin/dashboard'));
                exit;
            } else {
                $error = "Invalid username or password.";
                $this->render('login', ['error' => $error]);
            }
        } else {
            $this->render('login');
        }
    }

    public function dashboard() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        $submissionModel = new Submission();
        $submissions = $submissionModel->findAll();
        $this->render('dashboard', ['submissions' => $submissions]);
    }

    public function logout() {
        session_destroy();
        header('Location: ' . url('admin/login'));
        exit;
    }
}