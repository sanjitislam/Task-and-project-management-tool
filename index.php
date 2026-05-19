<?php
/**
 * =========================================
 * FRONT CONTROLLER — Application Entry Point
 * =========================================
 */

// ---------- App constants ----------
define('APP_RUNNING', true);
define('BASE_URL', '/task_management/');

// ---------- Error reporting ----------
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---------- Session setup ----------
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
session_start();

// ---------- Load core dependencies ----------
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/ActivityLogger.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/validation.php';

// ---------- Parse URL ----------
$url = $_GET['url'] ?? 'login';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$parts = explode('/', $url);

$controllerKey = strtolower($parts[0] ?? 'login');
$methodName    = $parts[1] ?? 'index';
$params        = array_slice($parts, 2);

// ---------- Route Map ----------
$routes = [
    'login'         => ['AuthController',         'login'],
    'logout'        => ['AuthController',         'logout'],
    'dashboard'     => ['DashboardController',    'index'],
    'workspaces'    => ['WorkspaceController',    'index'],
    'users'         => ['UserController',         'index'],
    'projects'      => ['ProjectController',      'index'],
    'tasks'         => ['TaskController',         'index'],
    'activity_logs' => ['ActivityLogController',  'index'],
    'support_tickets'=> ['SupportTicketController','index'],
    'analytics'     => ['AnalyticsController',    'index'],
    'settings'      => ['SettingsController',     'index'],

];

// ---------- Dispatch ----------
if (!isset($routes[$controllerKey])) {
    http_response_code(404);
    die('<h1>404 — Page Not Found</h1><p>Requested URL: ' . e($url) . '</p>');
}

[$controllerName, $defaultMethod] = $routes[$controllerKey];

if (!isset($parts[1])) {
    $methodName = $defaultMethod;
}

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

call_user_func_array([$controller, $methodName], $params);