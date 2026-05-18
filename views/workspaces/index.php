<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php
$workspaces = $workspaces ?? [];
$search = $search ?? '';
?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>🏢 Workspace Management</h2>
    <span class="badge"><?= count($workspaces) ?> workspace(s)</span>
</div>

<!-- Search Box -->
<div class="card search-card">
    <div class="search-bar">
        <input type="text"
               id="workspaceSearch"
               placeholder="🔍 Search workspaces by name or description..."
               value="<?= e($search) ?>"
               autocomplete="off">
        <span class="search-indicator" id="searchIndicator"></span>
    </div>
</div>

<!-- Workspaces Table -->
<div class="card">
    <div id="workspaceList">
        <?php if (empty($workspaces)): ?>
            <p class="empty-state">No workspaces found.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Owner</th>
                        <th>Plan</th>
                        <th>Members</th>
                        <th>Projects</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workspaces as $w): ?>
                        <tr data-id="<?= e($w['id']) ?>">
                            <td>
                                <strong><?= e($w['name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= e($w['invite_code']) ?></small>
                            </td>
                            <td>
                                <?= e($w['owner_name'] ?? 'N/A') ?>
                                <br>
                                <small class="text-muted"><?= e($w['owner_email'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($w['plan']) ?>">
                                    <?= e(ucfirst($w['plan'])) ?>
                                </span>
                            </td>
                            <td><?= e($w['member_count']) ?></td>
                            <td><?= e($w['project_count']) ?></td>
                            <td>
                                <button class="toggle-status <?= $w['is_active'] ? 'active' : 'inactive' ?>"
                                        data-id="<?= e($w['id']) ?>"
                                        title="Click to toggle">
                                    <?= $w['is_active'] ? '✅ Active' : '⛔ Inactive' ?>
                                </button>
                            </td>
                            <td><?= e(date('M d, Y', strtotime($w['created_at']))) ?></td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>workspaces/members/<?= e($w['id']) ?>"
                                   class="btn btn-secondary btn-small">
                                    👥 Members
                                </a>
                                <a href="<?= BASE_URL ?>workspaces/delete/<?= e($w['id']) ?>?csrf_token=<?= e(csrf_token()) ?>"
                                   class="btn btn-danger btn-small confirm-delete"
                                   data-name="<?= e($w['name']) ?>">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>