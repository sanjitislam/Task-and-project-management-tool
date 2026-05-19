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
require_once __DIR__ . '/../models/TaskModel.php';

header('Content-Type: application/json');

if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$filters = [
    'status'      => $_GET['status']      ?? '',
    'priority'    => $_GET['priority']    ?? '',
    'assignee_id' => $_GET['assignee_id'] ?? '',
    'project_id'  => $_GET['project_id']  ?? ''
];

try {
    $model = new TaskModel();
    $tasks = $model->getAll($filters);

    ob_start();
    if (empty($tasks)) {
        echo '<p class="empty-state">No tasks match your filters.</p>';
    } else {
        echo '<table class="data-table"><thead><tr>';
        echo '<th>Title</th><th>Project</th><th>Workspace</th><th>Assignee</th>';
        echo '<th>Priority</th><th>Status</th><th>Due Date</th><th>Created</th><th>Actions</th>';
        echo '</tr></thead><tbody>';

        foreach ($tasks as $t) {
            echo '<tr>';
            echo '<td><strong>' . e($t['title']) . '</strong>';
            if (!empty($t['milestone_title'])) {
                echo '<br><small class="text-muted">🎯 ' . e($t['milestone_title']) . '</small>';
            }
            echo '</td>';
            echo '<td>' . e($t['project_name'] ?? '—') . '</td>';
            echo '<td>' . e($t['workspace_name'] ?? '—') . '</td>';
            echo '<td>' . ($t['assignee_name']
                ? e($t['assignee_name'])
                : '<span class="text-muted">Unassigned</span>') . '</td>';
            echo '<td><span class="badge badge-priority-' . e($t['priority']) . '">';
            echo e(ucfirst($t['priority'])) . '</span></td>';
            echo '<td><span class="badge badge-task-' . e($t['status']) . '">';
            echo e(ucfirst(str_replace('_',' ',$t['status']))) . '</span></td>';
            echo '<td>' . ($t['due_date']
                ? e(date('M d, Y', strtotime($t['due_date'])))
                : '<span class="text-muted">—</span>') . '</td>';
            echo '<td>' . e(date('M d', strtotime($t['created_at']))) . '</td>';
            echo '<td class="actions">';
            echo '<a href="' . BASE_URL . 'tasks/details/' . e($t['id']) . '" class="btn btn-secondary btn-small">👁️ View</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'count'   => count($tasks),
        'html'    => $html
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>