<?php
/**
 * Auth Controller
 * --------------------------------------
 * Handles login and logout for the Admin module.
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class AuthController extends Controller
{
    /**
     * GET  /login → show the form
     * POST /login → validate, log in
     */
    public function login()
    {
        // If already logged in as admin, send them to dashboard
        if (Auth::check() && Auth::role() === 'admin') {
            redirect('dashboard');
        }

        $errors = [];
        $email  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // -------- CSRF protection --------
            if (!verify_csrf($_POST['csrf_token'] ?? '')) {
                set_flash('error', 'Invalid form submission. Please try again.');
                redirect('login');
            }

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // -------- Validation --------
            $errors = validate($_POST, [
                'email'    => 'required|email',
                'password' => 'required|min:6'
            ]);

            if (empty($errors)) {
                $userModel = $this->model('UserModel');
                $result = $userModel->verifyLogin($email, $password);

                if ($result === 'inactive') {
                    $errors['general'] = 'Your account is deactivated. Contact support.';
                } elseif (!$result) {
                    $errors['general'] = 'Invalid email or password.';
                } elseif ($result['role'] !== 'admin') {
                    // ✅ Admin module — only admins allowed
                    $errors['general'] = 'This login is for administrators only.';
                } else {
                    // ✅ Success
                     Auth::login($result);
                     ActivityLogger::log('user_login', 'Admin logged in: ' . $result['email']);
                     set_flash('success', 'Welcome back, ' . $result['name'] . '!');
                     redirect('dashboard');
                }
            }
        }

        // Render the login view
        $this->view('auth/login', [
            'errors' => $errors,
            'email'  => $email
        ]);
    }

    /**
     * GET /logout → destroy session, send to login
     */
    public function logout()
    {
         // Log BEFORE destroying session (so we still have the user ID)
    if (Auth::check()) {
        ActivityLogger::log('user_logout', 'Admin logged out');
    }
    Auth::logout();
    set_flash('success', 'You have been logged out.');
    redirect('login');
    }
}


?>