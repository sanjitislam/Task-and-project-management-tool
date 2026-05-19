<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>📊 Workspace Usage Report</h2>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>analytics" class="btn btn-primary btn-small active-tab">
            📊 Workspace Usage
        </a>
        <a href="<?= BASE_URL ?>analytics/platform" class="btn btn-secondary btn-small">
            📈 Platform Analytics
        </a>
    </div>
</div>

<div class="alert alert-info">
    💡 <strong>Note:</strong> Storage is estimated at 250 KB per attachment
    since the attachments table doesn't store actual file sizes.
</div>

<div class="card">
    <?php if (empty($usage)): ?>
        <p class="empty-state">No workspaces yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Workspace</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Members</th>
                    <th>Projects</th>
                    <th>Active Projects</th>
                    <th>Tasks</th>
                    <th>Done</th>
                    <th>Hours Logged</th>
                    <th>Attachments</th>
                    <th>Storage (est.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usage as $w): ?>
                    <tr>
                        <td><strong><?= e($w['name']) ?></strong></td>
                        <td>
                            <span class="badge badge-<?= e($w['plan']) ?>">
                                <?= e(ucfirst($w['plan'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($w['is_active']): ?>
                                <span class="status-active">✅ Active</span>
                            <?php else: ?>
                                <span class="status-inactive">⛔ Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($w['member_count']) ?></td>
                        <td><?= e($w['project_count']) ?></td>
                        <td><?= e($w['active_projects']) ?></td>
                        <td><?= e($w['task_count']) ?></td>
                        <td>
                            <?= e($w['done_count']) ?>
                            <?php if ($w['task_count'] > 0): ?>
                                <small class="text-muted">
                                    (<?= round(($w['done_count'] / $w['task_count']) * 100) ?>%)
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?= e(number_format($w['hours_logged'], 1)) ?> hrs</td>
                        <td><?= e($w['attachment_count']) ?></td>
                        <td>
                            <?php if ($w['storage_mb'] >= 1): ?>
                                <?= e($w['storage_mb']) ?> MB
                            <?php elseif ($w['storage_kb'] > 0): ?>
                                <?= e($w['storage_kb']) ?> KB
                            <?php else: ?>
                                <span class="text-muted">0 KB</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>