<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
// Ensure $tasks is defined to avoid undefined variable errors in views
if (!isset($tasks) || !is_array($tasks)) {
    $tasks = [];
}
// Ensure $filters is defined to avoid undefined variable errors in views
if (!isset($filters) || !is_array($filters)) {
    $filters = [
        'status' => '',
        'priority' => '',
        'assignee_id' => '',
        'project_id' => '',
    ];
}
// Ensure $assignees is defined to avoid undefined variable errors in views
if (!isset($assignees) || !is_array($assignees)) {
    $assignees = [];
}
// Ensure $projects is defined to avoid undefined variable errors in views
if (!isset($projects) || !is_array($projects)) {
    $projects = [];
}

?>

<div class="page-header">
    <h2>✅ Tasks Management</h2>
    <span class="badge"><?= count($tasks) ?> task(s)</span>
</div>

<!-- Filter Bar -->
<div class="card filter-card">
    <div class="filter-row">

        <div class="filter-group">
            <label>Status</label>
            <select id="taskStatusFilter" class="filter-select">
                <option value="">All statuses</option>
                <option value="todo"        <?= $filters['status']==='todo'        ?'selected':'' ?>>To Do</option>
                <option value="in_progress" <?= $filters['status']==='in_progress' ?'selected':'' ?>>In Progress</option>
                <option value="review"      <?= $filters['status']==='review'      ?'selected':'' ?>>Review</option>
                <option value="done"        <?= $filters['status']==='done'        ?'selected':'' ?>>Done</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Priority</label>
            <select id="taskPriorityFilter" class="filter-select">
                <option value="">All priorities</option>
                <option value="low"      <?= $filters['priority']==='low'      ?'selected':'' ?>>Low</option>
                <option value="medium"   <?= $filters['priority']==='medium'   ?'selected':'' ?>>Medium</option>
                <option value="high"     <?= $filters['priority']==='high'     ?'selected':'' ?>>High</option>
                <option value="critical" <?= $filters['priority']==='critical' ?'selected':'' ?>>Critical</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Assignee</label>
            <select id="taskAssigneeFilter" class="filter-select">
                <option value="">All assignees</option>
                <?php foreach ($assignees as $a): ?>
                    <option value="<?= e($a['id']) ?>"
                            <?= $filters['assignee_id']==$a['id'] ? 'selected':'' ?>>
                        <?= e($a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Project</label>
            <select id="taskProjectFilter" class="filter-select">
                <option value="">All projects</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= e($p['id']) ?>"
                            <?= $filters['project_id']==$p['id'] ? 'selected':'' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>&nbsp;</label>
            <button id="clearTaskFilters" class="btn btn-secondary btn-small">
                ✖ Clear
            </button>
        </div>

        <div class="filter-group filter-indicator">
            <span class="search-indicator" id="taskFilterIndicator"></span>
        </div>

    </div>
</div>

<!-- Tasks Table -->
<div class="card">
    <div id="taskList">
        <?php if (empty($tasks)): ?>
            <p class="empty-state">No tasks found. Try clearing filters.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Workspace</th>
                        <th>Assignee</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td>
                                <strong><?= e($t['title']) ?></strong>
                                <?php if (!empty($t['milestone_title'])): ?>
                                    <br><small class="text-muted">🎯 <?= e($t['milestone_title']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($t['project_name'] ?? '—') ?></td>
                            <td><?= e($t['workspace_name'] ?? '—') ?></td>
                            <td>
                                <?php if ($t['assignee_name']): ?>
                                    <?= e($t['assignee_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-priority-<?= e($t['priority']) ?>">
                                    <?= e(ucfirst($t['priority'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-task-<?= e($t['status']) ?>">
                                    <?= e(ucfirst(str_replace('_',' ',$t['status']))) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($t['due_date']): ?>
                                    <?= e(date('M d, Y', strtotime($t['due_date']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e(date('M d', strtotime($t['created_at']))) ?></td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>tasks/details/<?= e($t['id']) ?>"
                                   class="btn btn-secondary btn-small">
                                    👁️ View
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