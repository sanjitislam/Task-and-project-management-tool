<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php if (!isset($workspace)): ?>
    <?php $workspace = ['id' => '', 'name' => 'Workspace', 'owner_name' => 'N/A']; ?>
<?php endif; ?>

<div class="page-header">
    <div>
        <h2>👥 Members of "<?= e($workspace['name']) ?>"</h2>
        <p class="text-muted">Owner: <?= e($workspace['owner_name'] ?? 'N/A') ?></p>
    </div>
    <a href="<?= BASE_URL ?>workspaces" class="btn btn-secondary btn-small">
        ← Back to Workspaces
    </a>
</div>

<div class="card">
    <?php if (empty($members)): ?>
        <p class="empty-state">No members in this workspace yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Workspace Role</th>
                    <th>Global Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td><strong><?= e($m['name']) ?></strong></td>
                        <td><?= e($m['email']) ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?= e(ucfirst($m['workspace_role'])) ?>
                            </span>
                        </td>
                        <td><?= e(ucfirst($m['global_role'])) ?></td>
                        <td>
                            <?php if ($m['is_active']): ?>
                                <span class="status-active">✅ Active</span>
                            <?php else: ?>
                                <span class="status-inactive">⛔ Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e(date('M d, Y', strtotime($m['joined_at']))) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>workspaces/removeMember/<?= e($workspace['id']) ?>/<?= e($m['user_id']) ?>?csrf_token=<?= e(csrf_token()) ?>"
                               class="btn btn-danger btn-small confirm-remove"
                               data-name="<?= e($m['name']) ?>">
                                🚫 Remove
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>