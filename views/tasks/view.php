<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php
// Ensure $task is defined to avoid undefined variable notices in views
if (!isset($task) || !is_array($task)) {
    $task = [];
}
?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <div>
        <h2>✅ <?= e($task['title']) ?></h2>
        <p class="text-muted">
            Project:
            <a href="<?= BASE_URL ?>projects/details/<?= e($task['project_id']) ?>">
                <?= e($task['project_name'] ?? '—') ?>
            </a>
            &nbsp;|&nbsp;
            Workspace: <?= e($task['workspace_name'] ?? '—') ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>tasks" class="btn btn-secondary btn-small">
        ← Back to Tasks
    </a>
</div>

<!-- Task Info -->
<div class="card">
    <h3>📋 Task Details</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Description</span>
            <span class="info-value"><?= e($task['description'] ?: '— No description —') ?></span>
        </div>

        <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value">
                <span class="badge badge-task-<?= e($task['status']) ?>">
                    <?= e(ucfirst(str_replace('_',' ',$task['status']))) ?>
                </span>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Priority</span>
            <span class="info-value">
                <span class="badge badge-priority-<?= e($task['priority']) ?>">
                    <?= e(ucfirst($task['priority'])) ?>
                </span>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Assigned To</span>
            <span class="info-value">
                <?php if ($task['assignee_name']): ?>
                    <?= e($task['assignee_name']) ?>
                    <small class="text-muted">(<?= e($task['assignee_email']) ?>)</small>
                <?php else: ?>
                    <span class="text-muted">Unassigned</span>
                <?php endif; ?>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Created By</span>
            <span class="info-value"><?= e($task['creator_name'] ?? '—') ?></span>
        </div>

        <div class="info-item">
            <span class="info-label">Milestone</span>
            <span class="info-value">
                <?= $task['milestone_title']
                    ? '🎯 ' . e($task['milestone_title'])
                    : '<span class="text-muted">— None —</span>' ?>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Due Date</span>
            <span class="info-value">
                <?= $task['due_date']
                    ? e(date('F d, Y', strtotime($task['due_date'])))
                    : '<span class="text-muted">Not set</span>' ?>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Estimated Hours</span>
            <span class="info-value">
                <?= $task['estimated_hours']
                    ? e($task['estimated_hours']) . ' hrs'
                    : '<span class="text-muted">—</span>' ?>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Hours Logged</span>
            <span class="info-value">
                <strong><?= e($task['total_hours_logged']) ?> hrs</strong>
                <?php if ($task['estimated_hours'] && $task['total_hours_logged'] > 0): ?>
                    <small class="text-muted">
                        (<?= round(($task['total_hours_logged'] / $task['estimated_hours']) * 100) ?>%
                        of estimated)
                    </small>
                <?php endif; ?>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Created</span>
            <span class="info-value">
                <?= e(date('F d, Y g:i A', strtotime($task['created_at']))) ?>
            </span>
        </div>
    </div>
</div>

<!-- Comments -->
<div class="card">
    <h3>💬 Comments (<?= count($task['comments']) ?>)</h3>
    <?php if (empty($task['comments'])): ?>
        <p class="empty-state">No comments yet.</p>
    <?php else: ?>
        <div class="comments-list">
            <?php foreach ($task['comments'] as $cm): ?>
                <div class="comment-item <?= $cm['is_internal'] ? 'comment-internal' : '' ?>">
                    <div class="comment-header">
                        <strong><?= e($cm['user_name'] ?? '—') ?></strong>
                        <span class="badge badge-info">
                            <?= e(ucfirst(str_replace('_',' ',$cm['user_role'] ?? ''))) ?>
                        </span>
                        <?php if ($cm['is_internal']): ?>
                            <span class="badge badge-warning">🔒 Internal</span>
                        <?php endif; ?>
                        <small class="text-muted">
                            <?= e(date('M d, Y g:i A', strtotime($cm['created_at']))) ?>
                        </small>
                    </div>
                    <div class="comment-body">
                        <?= e($cm['body']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Time Logs -->
<div class="card">
    <h3>⏱️ Time Logs (<?= count($task['time_logs']) ?>)</h3>
    <?php if (empty($task['time_logs'])): ?>
        <p class="empty-state">No hours logged yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Hours</th>
                    <th>Note</th>
                    <th>Logged</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($task['time_logs'] as $tl): ?>
                    <tr>
                        <td><?= e($tl['user_name'] ?? '—') ?></td>
                        <td><strong><?= e($tl['hours_logged']) ?> hrs</strong></td>
                        <td><?= e($tl['note'] ?: '—') ?></td>
                        <td><?= e(date('M d, Y g:i A', strtotime($tl['logged_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Attachments -->
<div class="card">
    <h3>📎 Attachments (<?= count($task['attachments']) ?>)</h3>
    <?php if (empty($task['attachments'])): ?>
        <p class="empty-state">No files attached.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Uploaded By</th>
                    <th>Uploaded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($task['attachments'] as $at): ?>
                    <tr>
                        <td>📄 <?= e($at['file_name']) ?></td>
                        <td><?= e($at['uploader_name'] ?? '—') ?></td>
                        <td><?= e(date('M d, Y', strtotime($at['uploaded_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>