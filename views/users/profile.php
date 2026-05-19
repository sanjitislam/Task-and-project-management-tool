<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
$user = $user ?? [
    'name' => '',
    'email' => '',
    'role' => 'guest',
    'is_active' => false,
    'phone' => '',
    'created_at' => date('Y-m-d'),
    'workspaces' => [],
    'project_count' => 0,
    'task_count' => 0,
];

if (!is_array($user['workspaces'])) {
    $user['workspaces'] = [];
}
?>

<div class="page-header">
    <h2>👤 User Profile</h2>
    <a href="<?= BASE_URL ?>users" class="btn btn-secondary btn-small">
        ← Back to Users
    </a>
</div>

<div class="profile-grid">

    <!-- Left: User info -->
    <div class="card profile-card">
        <div class="profile-avatar">
            <?= e(strtoupper(substr($user['name'], 0, 1))) ?>
        </div>
        <h3><?= e($user['name']) ?></h3>
        <p class="profile-email"><?= e($user['email']) ?></p>

        <div class="profile-meta">
            <div class="profile-item">
                <span class="meta-label">Role</span>
                <span class="meta-value badge badge-role-<?= e($user['role']) ?>">
                    <?= e(ucfirst(str_replace('_', ' ', $user['role']))) ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="meta-label">Status</span>
                <span class="meta-value">
                    <?php if ($user['is_active']): ?>
                        <span class="status-active">✅ Active</span>
                    <?php else: ?>
                        <span class="status-inactive">⛔ Inactive</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="meta-label">Phone</span>
                <span class="meta-value"><?= e($user['phone'] ?: '—') ?></span>
            </div>

            <div class="profile-item">
                <span class="meta-label">Joined</span>
                <span class="meta-value">
                    <?= e(date('F d, Y', strtotime($user['created_at']))) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Right: Stats + Workspaces -->
    <div>
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                    <span class="stat-label">Workspaces</span>
                    <span class="stat-value"><?= count($user['workspaces']) ?></span>
                </div>
            </div>

            <div class="stat-card stat-orange">
                <div class="stat-icon">📁</div>
                <div class="stat-info">
                    <span class="stat-label">Projects</span>
                    <span class="stat-value"><?= e($user['project_count']) ?></span>
                </div>
            </div>

            <div class="stat-card stat-purple">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <span class="stat-label">Assigned Tasks</span>
                    <span class="stat-value"><?= e($user['task_count']) ?></span>
                </div>
            </div>
        </div>

        <!-- Workspaces list -->
        <div class="card">
            <h3>🏢 Workspace Memberships</h3>
            <?php if (empty($user['workspaces'])): ?>
                <p class="empty-state">This user has not joined any workspaces.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Workspace</th>
                            <th>Role</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user['workspaces'] as $w): ?>
                            <tr>
                                <td><strong><?= e($w['name']) ?></strong></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= e(ucfirst($w['workspace_role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= e($w['plan']) ?>">
                                        <?= e(ucfirst($w['plan'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($w['is_active']): ?>
                                        <span class="status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(date('M d, Y', strtotime($w['joined_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>