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
require_once __DIR__ . '/../models/WorkspaceModel.php';

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
    echo json_encode(['success' => false, 'message' => 'Invalid workspace ID']);
    exit;
}

try {
    $model = new WorkspaceModel();

    if (!$model->findById($id)) {
        echo json_encode(['success' => false, 'message' => 'Workspace not found']);
        exit;
    }

    $model->toggleActive($id);
    $newStatus = $model->getActiveStatus($id);

    // ← NEW: Log the action
    $action = $newStatus ? 'workspace_activated' : 'workspace_deactivated';
    $ws = $model->findById($id);
    ActivityLogger::log(
        $action,
        'Workspace ' . ($newStatus ? 'activated' : 'deactivated') . ': ' . ($ws['name'] ?? '?'),
        $id
    );

    echo json_encode([
        'success'    => true,
        'is_active'  => $newStatus,
        'message'    => $newStatus ? 'Workspace activated' : 'Workspace deactivated'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>