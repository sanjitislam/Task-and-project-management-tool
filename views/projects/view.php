<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
// Ensure $project exists and has sensible defaults to avoid undefined variable/index errors
$project = $project ?? [];
$project += [
    'name' => '',
    'workspace_name' => null,
    'status' => 'unknown',
    'description' => '',
    'client_name' => null,
    'client_email' => null,
    'deadline' => null,
    'visibility' => 'internal',
    'created_at' => date('Y-m-d'),
    'task_stats' => ['todo' => 0, 'in_progress' => 0, 'review' => 0, 'done' => 0],
    'members' => [],
    'milestones' => [],
];
?>

<div class="page-header">
    <div>
        <h2>📁 <?= e($project['name']) ?></h2>
        <p class="text-muted">
            Workspace: <?= e($project['workspace_name'] ?? '—') ?>
            &nbsp;|&nbsp;
            Status:
            <span class="badge badge-status-<?= e($project['status']) ?>">
                <?= e(ucfirst(str_replace('_',' ',$project['status']))) ?>
            </span>
        </p>
    </div>
    <a href="<?= BASE_URL ?>projects" class="btn btn-secondary btn-small">
        ← Back to Projects
    </a>
</div>

<!-- Project Info -->
<div class="card">
    <h3>📋 Project Details</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Description</span>
            <span class="info-value"><?= e($project['description'] ?: '— No description —') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Client</span>
            <span class="info-value">
                <?php if (!empty($project) && !empty($project['client_name'])): ?>
                    <?= e($project['client_name']) ?>
                    <small class="text-muted">(<?= e($project['client_email']) ?>)</small>
                <?php else: ?>
                    <span class="text-muted">No client assigned</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Deadline</span>
            <span class="info-value">
                <?= $project['deadline']
                    ? e(date('F d, Y', strtotime($project['deadline'])))
                    : '<span class="text-muted">Not set</span>' ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Visibility</span>
            <span class="info-value">
                <?= $project['visibility']==='client_visible'
                    ? '👁️ Client visible'
                    : '🔒 Internal only' ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Created</span>
            <span class="info-value">
                <?= e(date('M d, Y', strtotime($project['created_at']))) ?>
            </span>
        </div>
    </div>
</div>

<!-- Task Stats -->
<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-icon">📝</div>
        <div class="stat-info">
            <span class="stat-label">To Do</span>
            <span class="stat-value"><?= e($project['task_stats']['todo']) ?></span>
        </div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-icon">⚙️</div>
        <div class="stat-info">
            <span class="stat-label">In Progress</span>
            <span class="stat-value"><?= e($project['task_stats']['in_progress']) ?></span>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">🔍</div>
        <div class="stat-info">
            <span class="stat-label">In Review</span>
            <span class="stat-value"><?= e($project['task_stats']['review']) ?></span>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-label">Done</span>
            <span class="stat-value"><?= e($project['task_stats']['done']) ?></span>
        </div>
    </div>
</div>

<!-- Project Members -->
<div class="card">
    <h3>👥 Project Members (<?= count($project['members']) ?>)</h3>
    <?php if (empty($project['members'])): ?>
        <p class="empty-state">No members assigned to this project yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Workspace Role</th>
                    <th>Platform Role</th>
                    <th>Assigned</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($project['members'] as $m): ?>
                    <tr>
                        <td><strong><?= e($m['name']) ?></strong></td>
                        <td><?= e($m['email']) ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?= e(ucfirst($m['workspace_role'] ?? '—')) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-role-<?= e($m['role']) ?>">
                                <?= e(ucfirst(str_replace('_',' ',$m['role']))) ?>
                            </span>
                        </td>
                        <td><?= e(date('M d, Y', strtotime($m['assigned_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Milestones -->
<div class="card">
    <h3>🎯 Milestones (<?= count($project['milestones']) ?>)</h3>
    <?php if (empty($project['milestones'])): ?>
        <p class="empty-state">No milestones created for this project.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Visible to Client</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($project['milestones'] as $ms): ?>
                    <tr>
                        <td><strong><?= e($ms['title']) ?></strong></td>
                        <td><?= e($ms['description'] ?: '—') ?></td>
                        <td>
                            <?= $ms['due_date']
                                ? e(date('M d, Y', strtotime($ms['due_date'])))
                                : '—' ?>
                        </td>
                        <td>
                            <?php if ($ms['status'] === 'completed'): ?>
                                <span class="status-active">✅ Completed</span>
                            <?php else: ?>
                                <span class="text-muted">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $ms['is_client_visible'] ? '👁️ Yes' : '🔒 No' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>