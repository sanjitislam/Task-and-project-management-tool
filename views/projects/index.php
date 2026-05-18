<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<?php $filters = $filters ?? ['workspace_id' => '', 'status' => '', 'team_lead_id' => ''];
// ensure teamLeads is defined to avoid undefined variable errors in view
$teamLeads = $teamLeads ?? []; ?>

<div class="page-header">
    <h2>📁 Projects Management</h2>
    <span class="badge"><?= isset($projects) && is_array($projects) ? count($projects) : 0 ?> project(s)</span>
</div>

<!-- Filter Bar -->
<div class="card filter-card">
    <div class="filter-row">

        <div class="filter-group">
            <label>Workspace</label>
            <select id="projectWorkspaceFilter" class="filter-select">
                <option value="">All workspaces</option>
                <?php foreach ($workspaces ?? [] as $w): ?>
                    <option value="<?= e($w['id']) ?>"
                            <?= $filters['workspace_id']==$w['id'] ? 'selected':'' ?>>
                        <?= e($w['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Status</label>
            <select id="projectStatusFilter" class="filter-select">
                <option value="">All statuses</option>
                <option value="planning"  <?= $filters['status']==='planning' ?'selected':'' ?>>Planning</option>
                <option value="active"    <?= $filters['status']==='active'   ?'selected':'' ?>>Active</option>
                <option value="on_hold"   <?= $filters['status']==='on_hold'  ?'selected':'' ?>>On Hold</option>
                <option value="completed" <?= $filters['status']==='completed'?'selected':'' ?>>Completed</option>
                <option value="archived"  <?= $filters['status']==='archived' ?'selected':'' ?>>Archived</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Team Lead</label>
            <select id="projectLeadFilter" class="filter-select">
                <option value="">All team leads</option>
                <?php foreach ($teamLeads as $l): ?>
                    <option value="<?= e($l['id']) ?>"
                            <?= $filters['team_lead_id']==$l['id'] ? 'selected':'' ?>>
                        <?= e($l['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>&nbsp;</label>
            <button id="clearProjectFilters" class="btn btn-secondary btn-small">
                ✖ Clear Filters
            </button>
        </div>

        <div class="filter-group filter-indicator">
            <span class="search-indicator" id="projectFilterIndicator"></span>
        </div>

    </div>
</div>

<!-- Projects Table -->
<div class="card">
    <div id="projectList">
        <?php if (empty($projects)): ?>
            <p class="empty-state">No projects found. Try clearing your filters.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Workspace</th>
                        <th>Team Lead</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Members</th>
                        <th>Tasks</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                        <tr>
                            <td>
                                <strong><?= e($p['name']) ?></strong>
                                <?php if (!empty($p['color_label'])): ?>
                                    <span class="color-dot" style="background: <?= e($p['color_label']) ?>"></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($p['workspace_name'] ?? '—') ?></td>
                            <td><?= e($p['team_lead_name'] ?? '—') ?></td>
                            <td><?= e($p['client_name'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-status-<?= e($p['status']) ?>">
                                    <?= e(ucfirst(str_replace('_',' ',$p['status']))) ?>
                                </span>
                            </td>
                            <td><?= e($p['member_count']) ?></td>
                            <td><?= e($p['task_count']) ?></td>
                            <td>
                                <?php if ($p['deadline']): ?>
                                    <?= e(date('M d, Y', strtotime($p['deadline']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">No deadline</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>projects/details/<?= e($p['id']) ?>"
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