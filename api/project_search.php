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
require_once __DIR__ . '/../models/ProjectModel.php';

header('Content-Type: application/json');

if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$filters = [
    'workspace_id' => $_GET['workspace_id'] ?? '',
    'status'       => $_GET['status']       ?? '',
    'team_lead_id' => $_GET['team_lead_id'] ?? ''
];

try {
    $model = new ProjectModel();
    $projects = $model->getAll($filters);

    ob_start();
    if (empty($projects)) {
        echo '<p class="empty-state">No projects match your filters.</p>';
    } else {
        echo '<table class="data-table"><thead><tr>';
        echo '<th>Project</th><th>Workspace</th><th>Team Lead</th><th>Client</th>';
        echo '<th>Status</th><th>Members</th><th>Tasks</th><th>Deadline</th><th>Actions</th>';
        echo '</tr></thead><tbody>';

        foreach ($projects as $p) {
            echo '<tr>';
            echo '<td><strong>' . e($p['name']) . '</strong>';
            if (!empty($p['color_label'])) {
                echo ' <span class="color-dot" style="background:' . e($p['color_label']) . '"></span>';
            }
            echo '</td>';
            echo '<td>' . e($p['workspace_name'] ?? '—') . '</td>';
            echo '<td>' . e($p['team_lead_name'] ?? '—') . '</td>';
            echo '<td>' . e($p['client_name'] ?? '—') . '</td>';
            echo '<td><span class="badge badge-status-' . e($p['status']) . '">';
            echo e(ucfirst(str_replace('_',' ',$p['status']))) . '</span></td>';
            echo '<td>' . e($p['member_count']) . '</td>';
            echo '<td>' . e($p['task_count']) . '</td>';
            echo '<td>' . ($p['deadline']
                ? e(date('M d, Y', strtotime($p['deadline'])))
                : '<span class="text-muted">No deadline</span>') . '</td>';
            echo '<td class="actions">';
            echo '<a href="' . BASE_URL . 'projects/details/' . e($p['id']) . '" class="btn btn-secondary btn-small">👁️ View</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'count'   => count($projects),
        'html'    => $html
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>