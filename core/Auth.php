<?php
/**
 * Auth Helper
 * --------------------------------------
 * Manages login sessions and role-based access control.
 *
 * Used by every protected controller to ensure
 * the current user is logged in AND has the right role.
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class Auth
{
    /**
     * Check if anyone is currently logged in.
     */
    public static function check()
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get the logged-in user's data (or null).
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name']  ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role']  ?? ''
        ];
    }

    /**
     * Get logged-in user's ID quickly.
     */
    public static function id()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get logged-in user's role.
     */
    public static function role()
    {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Log a user in by storing their info in the session.
     */
    public static function login($user)
    {
        // Regenerate session ID — defends against session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['login_time'] = time();
    }

    /**
     * Destroy session and log out completely.
     */
    public static function logout()
    {
        $_SESSION = [];

        // Delete the session cookie from the browser
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Require user to be logged in — else redirect to login page.
     * Call this at the top of every protected controller method.
     */
    public static function requireLogin()
    {
        if (!self::check()) {
            set_flash('error', 'Please log in to continue.');
            redirect('login');
        }
    }

    /**
     * Require user to be a specific role (e.g., 'admin').
     * If they aren't — show "Access Denied" and stop.
     */
    public static function requireRole($role)
    {
        self::requireLogin();

        if (self::role() !== $role) {
            http_response_code(403);
            die('<h1>403 — Access Denied</h1><p>You do not have permission to view this page.</p>');
        }
    }
}

?>