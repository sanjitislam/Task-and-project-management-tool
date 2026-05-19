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
require_once __DIR__ . '/../core/ActivityLogger.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/ActivityLogModel.php';

header('Content-Type: application/json');

if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$filters = [
    'from_date'    => $_GET['from_date']    ?? '',
    'to_date'      => $_GET['to_date']      ?? '',
    'action_type'  => $_GET['action_type']  ?? '',
    'user_id'      => $_GET['user_id']      ?? '',
    'workspace_id' => $_GET['workspace_id'] ?? ''
];

try {
    $model = new ActivityLogModel();
    $logs = $model->getAll($filters);

    ob_start();
    if (empty($logs)) {
        echo '<p class="empty-state">No activity logs match your filters.</p>';
    } else {
        echo '<table class="data-table"><thead><tr>';
        echo '<th>Time</th><th>Action</th><th>Description</th>';
        echo '<th>User</th><th>Workspace</th><th>Project</th>';
        echo '</tr></thead><tbody>';

        foreach ($logs as $log) {
            echo '<tr>';
            echo '<td class="log-time">' . e(date('M d, Y', strtotime($log['created_at']))) . '<br>';
            echo '<small class="text-muted">' . e(date('g:i:s A', strtotime($log['created_at']))) . '</small></td>';

            echo '<td><span class="badge badge-info">';
            echo e(ActivityLogger::actionLabel($log['action_type'])) . '</span></td>';

            echo '<td class="log-description">' . e($log['description'] ?? '—') . '</td>';

            echo '<td>';
            if ($log['user_name']) {
                echo '<strong>' . e($log['user_name']) . '</strong><br>';
                echo '<small class="text-muted">' . e(ucfirst(str_replace('_',' ',$log['role'] ?? ''))) . '</small>';
            } else {
                echo '<span class="text-muted">System</span>';
            }
            echo '</td>';

            echo '<td>' . e($log['workspace_name'] ?? '—') . '</td>';
            echo '<td>' . e($log['project_name'] ?? '—') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'count'   => count($logs),
        'html'    => $html
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>