<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php $status = $status ?? ''; ?>
<?php $counts = $counts ?? ['open' => 0, 'resolved' => 0]; ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>🎫 Support Tickets</h2>
    <div class="ticket-stats">
        <span class="badge badge-status-open">
            🔴 <?= e($counts['open']) ?> Open
        </span>
        <span class="badge badge-status-resolved">
            ✅ <?= e($counts['resolved']) ?> Resolved
        </span>
    </div>
</div>

<!-- Filter -->
<div class="card filter-card">
    <div class="filter-row">
        <div class="filter-group">
            <label>Status</label>
            <select id="ticketStatusFilter" class="filter-select"
                    onchange="window.location.href='<?= BASE_URL ?>support_tickets' + (this.value ? '?status=' + this.value : '')">
                <option value="">All tickets</option>
                <option value="open"     <?= $status==='open'     ?'selected':'' ?>>Open only</option>
                <option value="resolved" <?= $status==='resolved' ?'selected':'' ?>>Resolved only</option>
            </select>
        </div>
    </div>
</div>

<!-- Tickets Table -->
<div class="card">
    <?php if (empty($tickets)): ?>
        <p class="empty-state">🎉 No tickets to show. All quiet!</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Submitter</th>
                    <th>Status</th>
                    <th>Has Notes</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr class="<?= $t['status']==='open' ? 'row-priority' : '' ?>">
                        <td>#<?= e($t['id']) ?></td>
                        <td>
                            <strong><?= e($t['subject'] ?? 'No subject') ?></strong>
                            <br>
                            <small class="text-muted">
                                <?= e(mb_strimwidth($t['message'] ?? '', 0, 70, '...')) ?>
                            </small>
                        </td>
                        <td>
                            <?php if ($t['user_name']): ?>
                                <?= e($t['user_name']) ?>
                                <br>
                                <small class="text-muted"><?= e($t['user_email']) ?></small>
                            <?php else: ?>
                                <span class="text-muted">Anonymous</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="toggle-ticket <?= e($t['status']) ?>"
                                    data-id="<?= e($t['id']) ?>"
                                    title="Click to toggle">
                                <?= $t['status'] === 'open' ? '🔴 Open' : '✅ Resolved' ?>
                            </button>
                        </td>
                        <td>
                            <?= !empty($t['admin_notes'])
                                ? '<span class="status-active">📝 Yes</span>'
                                : '<span class="text-muted">No</span>' ?>
                        </td>
                        <td>
                            <?= e(date('M d, Y', strtotime($t['created_at']))) ?>
                            <br>
                            <small class="text-muted">
                                <?= e(date('g:i A', strtotime($t['created_at']))) ?>
                            </small>
                        </td>
                        <td class="actions">
                            <a href="<?= BASE_URL ?>support_tickets/details/<?= e($t['id']) ?>"
                               class="btn btn-secondary btn-small">
                                📝 View / Notes
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>