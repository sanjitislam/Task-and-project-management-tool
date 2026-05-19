<?php
/**
 * AJAX endpoint — returns dashboard stats as JSON.
 * Called by app.js when admin clicks "Refresh Stats".
 */

define('APP_RUNNING', true);
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../helpers/functions.php';

// Set JSON response type
header('Content-Type: application/json');

// ---- Security: only logged-in admins can call this ----
if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

try {
    // Load Models
    require_once __DIR__ . '/../models/UserModel.php';
    require_once __DIR__ . '/../models/WorkspaceModel.php';
    require_once __DIR__ . '/../models/ProjectModel.php';
    require_once __DIR__ . '/../models/TaskModel.php';

    $userModel      = new UserModel();
    $workspaceModel = new WorkspaceModel();
    $projectModel   = new ProjectModel();
    $taskModel      = new TaskModel();

    $stats = [
        'total_workspaces' => $workspaceModel->countAll(),
        'total_users'      => $userModel->countAll(),
        'users_by_role'    => $userModel->countByRole(),
        'active_projects'  => $projectModel->countActive(),
        'tasks_today'      => $taskModel->countCreatedToday(),
        'updated_at'       => date('Y-m-d H:i:s')
    ];

    echo json_encode([
        'success' => true,
        'stats'   => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

?>