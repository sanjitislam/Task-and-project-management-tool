<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
// Ensure expected variables are defined to avoid undefined variable errors in views
$users  = $users  ?? [];
$search = $search ?? '';
$role   = $role   ?? '';
?>

<div class="page-header">
    <div>
        <h2>👥 User Management</h2>
        <span class="badge"><?= count($users) ?> user(s)</span>
    </div>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>users/createAdmin" class="btn btn-success btn-small">
            ➕ Create Admin
        </a>
        <a href="<?= BASE_URL ?>users/invite" class="btn btn-primary btn-small">
            📨 Invite User
        </a>
    </div>
</div>
<!-- Search + Filter Bar -->
<div class="card search-card">
    <div class="search-bar">
        <input type="text"
               id="userSearch"
               placeholder="🔍 Search by name or email..."
               value="<?= e($search) ?>"
               autocomplete="off">

        <select id="userRoleFilter" class="role-filter">
            <option value="">All roles</option>
            <option value="admin"     <?= $role === 'admin'     ? 'selected' : '' ?>>Admin</option>
            <option value="team_lead" <?= $role === 'team_lead' ? 'selected' : '' ?>>Team Lead</option>
            <option value="member"    <?= $role === 'member'    ? 'selected' : '' ?>>Member</option>
            <option value="client"    <?= $role === 'client'    ? 'selected' : '' ?>>Client</option>
        </select>

        <span class="search-indicator" id="userSearchIndicator"></span>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div id="userList">
        <?php if (empty($users)): ?>
            <p class="empty-state">No users found.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Workspaces</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr data-id="<?= e($u['id']) ?>">
                            <td>
                                <strong><?= e($u['name']) ?></strong>
                            </td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['phone'] ?: '—') ?></td>
                            <td>
                                <select class="role-select badge-role-<?= e($u['role']) ?>"
                                        data-id="<?= e($u['id']) ?>"
                                        data-current="<?= e($u['role']) ?>">
                                    <option value="admin"     <?= $u['role']==='admin'     ?'selected':'' ?>>Admin</option>
                                    <option value="team_lead" <?= $u['role']==='team_lead' ?'selected':'' ?>>Team Lead</option>
                                    <option value="member"    <?= $u['role']==='member'    ?'selected':'' ?>>Member</option>
                                    <option value="client"    <?= $u['role']==='client'    ?'selected':'' ?>>Client</option>
                                </select>
                            </td>
                            <td><?= e($u['workspace_count']) ?></td>
                            <td>
                                <button class="toggle-user-status <?= $u['is_active'] ? 'active' : 'inactive' ?>"
                                        data-id="<?= e($u['id']) ?>"
                                        title="Click to toggle">
                                    <?= $u['is_active'] ? '✅ Active' : '⛔ Inactive' ?>
                                </button>
                            </td>
                            <td><?= e(date('M d, Y', strtotime($u['created_at']))) ?></td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>users/profile/<?= e($u['id']) ?>"
                                   class="btn btn-secondary btn-small">
                                    👤 View
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