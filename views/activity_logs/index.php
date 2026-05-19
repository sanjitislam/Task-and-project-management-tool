<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
$filters = $filters ?? [
    'from_date' => '',
    'to_date' => '',
    'action_type' => '',
    'user_id' => '',
    'workspace_id' => '',
];
$actionTypes = $actionTypes ?? [];
$users = $users ?? [];
$workspaces = $workspaces ?? [];
$logs = $logs ?? [];
?>


<div class="page-header">
    <h2>📜 Activity Logs</h2>
    <span class="badge"><?= count($logs) ?> log entries</span>
</div>

<!-- Filter Bar -->
<div class="card filter-card">
    <div class="filter-row">

        <div class="filter-group">
            <label>From Date</label>
            <input type="date" id="logFromDate" class="filter-input"
                   value="<?= e($filters['from_date']) ?>">
        </div>

        <div class="filter-group">
            <label>To Date</label>
            <input type="date" id="logToDate" class="filter-input"
                   value="<?= e($filters['to_date']) ?>">
        </div>

        <div class="filter-group">
            <label>Action Type</label>
            <select id="logActionFilter" class="filter-select">
                <option value="">All actions</option>
                <?php foreach ($actionTypes as $a): ?>
                    <option value="<?= e($a['action_type']) ?>"
                            <?= $filters['action_type']===$a['action_type'] ? 'selected':'' ?>>
                        <?= e(ActivityLogger::actionLabel($a['action_type'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>User</label>
            <select id="logUserFilter" class="filter-select">
                <option value="">All users</option>
                <?php foreach ($users ?? [] as $u): ?>
                    <option value="<?= e($u['id']) ?>"
                            <?= $filters['user_id']==$u['id'] ? 'selected':'' ?>>
                        <?= e($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Workspace</label>
            <select id="logWorkspaceFilter" class="filter-select">
                <option value="">All workspaces</option>
                <?php foreach ($workspaces as $w): ?>
                    <option value="<?= e($w['id']) ?>"
                            <?= $filters['workspace_id']==$w['id'] ? 'selected':'' ?>>
                        <?= e($w['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>&nbsp;</label>
            <button id="clearLogFilters" class="btn btn-secondary btn-small">
                ✖ Clear
            </button>
        </div>

        <div class="filter-group filter-indicator">
            <span class="search-indicator" id="logFilterIndicator"></span>
        </div>

    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div id="logList">
        <?php if (empty($logs)): ?>
            <p class="empty-state">No activity logs match your filters.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>User</th>
                        <th>Workspace</th>
                        <th>Project</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="log-time">
                                <?= e(date('M d, Y', strtotime($log['created_at']))) ?>
                                <br>
                                <small class="text-muted">
                                    <?= e(date('g:i:s A', strtotime($log['created_at']))) ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?= e(ActivityLogger::actionLabel($log['action_type'])) ?>
                                </span>
                            </td>
                            <td class="log-description">
                                <?= e($log['description'] ?? '—') ?>
                            </td>
                            <td>
                                <?php if ($log['user_name']): ?>
                                    <strong><?= e($log['user_name']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= e(ucfirst(str_replace('_',' ',$log['role'] ?? ''))) ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">System</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($log['workspace_name'] ?? '—') ?></td>
                            <td><?= e($log['project_name'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (count($logs) >= 500): ?>
        <p class="text-muted" style="text-align:center; margin-top:14px;">
            ⚠️ Showing the 500 most recent entries. Narrow your filters to see older logs.
        </p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>