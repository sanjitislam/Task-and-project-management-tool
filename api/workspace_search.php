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
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/WorkspaceModel.php';

header('Content-Type: application/json');

if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$search = trim($_GET['q'] ?? '');

try {
    $model = new WorkspaceModel();
    $workspaces = $model->getAll($search);

    ob_start();
    if (empty($workspaces)) {
        echo '<p class="empty-state">No workspaces found.</p>';
    } else {
        echo '<table class="data-table"><thead><tr>';
        echo '<th>Name</th><th>Owner</th><th>Plan</th><th>Members</th>';
        echo '<th>Projects</th><th>Status</th><th>Created</th><th>Actions</th>';
        echo '</tr></thead><tbody>';

        foreach ($workspaces as $w) {
            $statusClass = $w['is_active'] ? 'active' : 'inactive';
            $statusText  = $w['is_active'] ? '✅ Active' : '⛔ Inactive';

            echo '<tr data-id="' . e($w['id']) . '">';
            echo '<td><strong>' . e($w['name']) . '</strong><br>';
            echo '<small class="text-muted">' . e($w['invite_code']) . '</small></td>';
            echo '<td>' . e($w['owner_name'] ?? 'N/A') . '<br>';
            echo '<small class="text-muted">' . e($w['owner_email'] ?? '') . '</small></td>';
            echo '<td><span class="badge badge-' . e($w['plan']) . '">';
            echo e(ucfirst($w['plan'])) . '</span></td>';
            echo '<td>' . e($w['member_count']) . '</td>';
            echo '<td>' . e($w['project_count']) . '</td>';
            echo '<td><button class="toggle-status ' . $statusClass . '" ';
            echo 'data-id="' . e($w['id']) . '">' . $statusText . '</button></td>';
            echo '<td>' . e(date('M d, Y', strtotime($w['created_at']))) . '</td>';
            echo '<td class="actions">';
            echo '<a href="' . BASE_URL . 'workspaces/members/' . e($w['id']) . '" class="btn btn-secondary btn-small">👥 Members</a> ';
            echo '<a href="' . BASE_URL . 'workspaces/delete/' . e($w['id']) . '?csrf_token=' . e(csrf_token()) . '" class="btn btn-danger btn-small confirm-delete" data-name="' . e($w['name']) . '">🗑️ Delete</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'count'   => count($workspaces),
        'html'    => $html
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>