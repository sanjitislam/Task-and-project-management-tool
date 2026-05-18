<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php
if (!isset($stats) || !is_array($stats)) {
    $stats = [
        'total_workspaces' => 0,
        'total_users' => 0,
        'active_projects' => 0,
        'tasks_today' => 0,
        'users_by_role' => [
            'admin' => 0,
            'team_lead' => 0,
            'member' => 0,
            'client' => 0,
        ],
    ];
}
?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>📊 Admin Dashboard</h2>
    <button id="refreshStats" class="btn btn-primary btn-small">
        🔄 Refresh Stats
    </button>
</div>

<!-- Stat cards row -->
<div class="stats-grid">

    <div class="stat-card stat-blue">
        <div class="stat-icon">🏢</div>
        <div class="stat-info">
            <span class="stat-label">Total Workspaces</span>
            <span class="stat-value" id="stat-workspaces">
                <?= e($stats['total_workspaces']) ?>
            </span>
        </div>
    </div>

    <div class="stat-card stat-green">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-label">Total Users</span>
            <span class="stat-value" id="stat-users">
                <?= e($stats['total_users']) ?>
            </span>
        </div>
    </div>

    <div class="stat-card stat-orange">
        <div class="stat-icon">📁</div>
        <div class="stat-info">
            <span class="stat-label">Active Projects</span>
            <span class="stat-value" id="stat-projects">
                <?= e($stats['active_projects']) ?>
            </span>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-label">Tasks Created Today</span>
            <span class="stat-value" id="stat-tasks-today">
                <?= e($stats['tasks_today']) ?>
            </span>
        </div>
    </div>

</div>

<!-- Users by Role breakdown -->
<div class="card">
    <h3>👥 Users Breakdown by Role</h3>
    <div class="role-grid">
        <div class="role-item">
            <span class="role-label">Admins</span>
            <span class="role-count" id="role-admin">
                <?= e($stats['users_by_role']['admin']) ?>
            </span>
        </div>
        <div class="role-item">
            <span class="role-label">Team Leads</span>
            <span class="role-count" id="role-team_lead">
                <?= e($stats['users_by_role']['team_lead']) ?>
            </span>
        </div>
        <div class="role-item">
            <span class="role-label">Members</span>
            <span class="role-count" id="role-member">
                <?= e($stats['users_by_role']['member']) ?>
            </span>
        </div>
        <div class="role-item">
            <span class="role-label">Clients</span>
            <span class="role-count" id="role-client">
                <?= e($stats['users_by_role']['client']) ?>
            </span>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>