<?php
define('APP_RUNNING', true);
session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/task_management/');
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/UserModel.php';

header('Content-Type: application/json');

if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$search = trim($_GET['q']    ?? '');
$role   = trim($_GET['role'] ?? '');

try {
    $model = new UserModel();
    $users = $model->getAll($search, $role);

    ob_start();
    if (empty($users)) {
        echo '<p class="empty-state">No users found.</p>';
    } else {
        echo '<table class="data-table"><thead><tr>';
        echo '<th>Name</th><th>Email</th><th>Phone</th><th>Role</th>';
        echo '<th>Workspaces</th><th>Status</th><th>Joined</th><th>Actions</th>';
        echo '</tr></thead><tbody>';

        foreach ($users as $u) {
            $statusClass = $u['is_active'] ? 'active' : 'inactive';
            $statusText  = $u['is_active'] ? '✅ Active' : '⛔ Inactive';

            echo '<tr data-id="' . e($u['id']) . '">';
            echo '<td><strong>' . e($u['name']) . '</strong></td>';
            echo '<td>' . e($u['email']) . '</td>';
            echo '<td>' . e($u['phone'] ?: '—') . '</td>';

            echo '<td><select class="role-select badge-role-' . e($u['role']) . '" ';
            echo 'data-id="' . e($u['id']) . '" data-current="' . e($u['role']) . '">';
            foreach (['admin','team_lead','member','client'] as $r) {
                $sel = $u['role'] === $r ? 'selected' : '';
                $label = ucfirst(str_replace('_',' ',$r));
                echo '<option value="' . $r . '" ' . $sel . '>' . $label . '</option>';
            }
            echo '</select></td>';

            echo '<td>' . e($u['workspace_count']) . '</td>';
            echo '<td><button class="toggle-user-status ' . $statusClass . '" ';
            echo 'data-id="' . e($u['id']) . '">' . $statusText . '</button></td>';
            echo '<td>' . e(date('M d, Y', strtotime($u['created_at']))) . '</td>';
            echo '<td class="actions">';
            echo '<a href="' . BASE_URL . 'users/profile/' . e($u['id']) . '" class="btn btn-secondary btn-small">👤 View</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'count'   => count($users),
        'html'    => $html
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>