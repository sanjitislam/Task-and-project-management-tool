<?php
define('APP_RUNNING', true);
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/task_management/');
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/ActivityLogger.php';      // ← NEW
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/UserModel.php';

header('Content-Type: application/json');

if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['csrf_token'] ?? '';

if (!verify_csrf($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = (int)($input['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Safety: don't let admin deactivate themselves
if ($id === (int)Auth::id()) {
    echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account.']);
    exit;
}

try {
    $model = new UserModel();

    if (!$model->findById($id)) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $model->toggleActive($id);
    $newStatus = $model->getActiveStatus($id);

    // ← NEW: Log the action
    $action = $newStatus ? 'user_activated' : 'user_deactivated';
    ActivityLogger::log(
        $action,
        'User ' . ($newStatus ? 'activated' : 'deactivated') . ' (ID: ' . $id . ')'
    );

    echo json_encode([
        'success'   => true,
        'is_active' => $newStatus,
        'message'   => $newStatus ? 'User activated' : 'User deactivated'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>