<?php
define('APP_RUNNING', true);
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

if (!verify_csrf($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id      = (int)($input['id']   ?? 0);
$newRole = trim($input['role']  ?? '');

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$allowed = ['admin', 'team_lead', 'member', 'client'];
if (!in_array($newRole, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

if ($id === (int)Auth::id() && $newRole !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'You cannot change your own role.']);
    exit;
}

try {
    $model = new UserModel();
    $user = $model->findById($id);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($model->changeRole($id, $newRole)) {

        // ← NEW: Log the action
        ActivityLogger::log(
            'user_role_changed',
            'User ID ' . $id . ' role changed to: ' . $newRole
        );

        echo json_encode([
            'success'  => true,
            'message'  => 'Role updated to ' . ucfirst(str_replace('_',' ',$newRole)),
            'new_role' => $newRole
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update role']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>