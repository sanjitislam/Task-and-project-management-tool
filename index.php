<?php
/**
 * =========================================
 * FRONT CONTROLLER — Application Entry Point
 * =========================================
 *
 * Every URL in the app is routed through this file by .htaccess.
 * It decides which Controller method to call.
 *
 * Example:
 *    /login           → AuthController->login()
 *    /dashboard       → DashboardController->index()
 *    /workspaces      → WorkspaceController->index()
 *    /users/profile/5 → UserController->profile(5)
 */

// ---------- App constants ----------
define('APP_RUNNING', true);
define('BASE_URL', '/task_management/');

// ---------- Error reporting (development) ----------
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---------- Session setup ----------
// Secure session cookies BEFORE session_start()
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);  // JS can't read the session cookie
session_start();

// ---------- Load core dependencies ----------
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/validation.php';

// ---------- Parse URL ----------
$url = $_GET['url'] ?? 'login';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$parts = explode('/', $url);

// First segment = controller name (e.g. "login", "dashboard")
// Second segment = method (defaults to "index")
// Remaining = parameters
$controllerKey = strtolower($parts[0] ?? 'login');
$methodName    = $parts[1] ?? 'index';
$params        = array_slice($parts, 2);

// ---------- Route Map ----------
// Maps URL keywords to actual Controller + method
$routes = [
    'login'     => ['AuthController',      'login'],
    'logout'    => ['AuthController',      'logout'],
    'dashboard' => ['DashboardController', 'index'],
    'workspaces' => ['WorkspaceController', 'index'],
    'users'     => ['UserController',      'index'],
    'projects'  => ['ProjectController',   'index'],
];

// ---------- Dispatch ----------
if (!isset($routes[$controllerKey])) {
    http_response_code(404);
    die('<h1>404 — Page Not Found</h1><p>Requested URL: ' . e($url) . '</p>');
}

[$controllerName, $defaultMethod] = $routes[$controllerKey];

// If user explicitly supplied a method (e.g. /users/profile/5), use it
// Otherwise use the route's default
if (!isset($parts[1])) {
    $methodName = $defaultMethod;
}

// Load the controller class
$controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    die("Controller file missing: $controllerName");
}

require_once $controllerFile;

$controller = new $controllerName();

if (!method_exists($controller, $methodName)) {
    http_response_code(404);
    die("Method '$methodName' not found in $controllerName");
}

// Call the controller method (passing any URL params)
call_user_func_array([$controller, $methodName], $params);

?>