<?php
/**
 * Global Helper Functions
 * --------------------------------------
 * Small reusable utilities used across the app.
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

/**
 * Safely redirect to another URL.
 */
function redirect($path)
{
    $baseUrl = defined('BASE_URL') ? BASE_URL : '/';
    header("Location: " . rtrim($baseUrl, '/') . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Escape output to prevent XSS attacks.
 * Use this whenever you echo data from the database into HTML.
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}


/**
 * UTF-8 safe string length with a fallback when mbstring is not enabled.
 */
function mb_safe_strlen($string)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($string, 'UTF-8');
    }
    return strlen($string);
}

/**
 * Create a safe text excerpt with fallback when mbstring is not enabled.
 */
function text_excerpt($string, $length = 70, $suffix = '...')
{
    $string = (string)($string ?? '');
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($string, 0, $length, $suffix, 'UTF-8');
    }
    return strlen($string) > $length ? substr($string, 0, max(0, $length - strlen($suffix))) . $suffix : $string;
}

/**
 * Generate or get the CSRF token for this session.
 * CSRF = Cross-Site Request Forgery — prevents fake form submissions
 * from other websites.
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token matches what we issued.
 */
function verify_csrf($token)
{
    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set a "flash message" — shown ONCE on the next page load.
 * Useful for "Login successful!" or "Workspace deleted" messages.
 */
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message.
 */
function get_flash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Get old form value (used to repopulate forms after validation error).
 */
function old($key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

/**
 * Save form input to session (for repopulating after error).
 */
function flash_old($data)
{
    $_SESSION['old'] = $data;
}

/**
 * Clear old form data (call after form is successfully shown).
 */
function clear_old()
{
    unset($_SESSION['old']);
}


/**
 * Generate a random secure password.
 * Used when admin invites a new user — they get a temp password to log in with.
 */
function generate_random_password($length = 10)
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
    // Excluded confusing chars: I/l/1, O/0
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Generate a random workspace invite code.
 */
function generate_invite_code($length = 8)
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

?>