<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php $ticket = $ticket ?? []; ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <div>
        <h2>🎫 Ticket #<?= e($ticket['id']) ?></h2>
        <p class="text-muted">
            Status:
            <button class="toggle-ticket <?= e($ticket['status']) ?>"
                    data-id="<?= e($ticket['id']) ?>">
                <?= $ticket['status'] === 'open' ? '🔴 Open' : '✅ Resolved' ?>
            </button>
        </p>
    </div>
    <a href="<?= BASE_URL ?>support_tickets" class="btn btn-secondary btn-small">
        ← Back to Tickets
    </a>
</div>

<!-- Submitter Info -->
<div class="card">
    <h3>👤 Submitted By</h3>
    <?php if ($ticket['user_name']): ?>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Name</span>
                <span class="info-value"><?= e($ticket['user_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email</span>
                <span class="info-value"><?= e($ticket['user_email']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Role</span>
                <span class="info-value">
                    <span class="badge badge-role-<?= e($ticket['user_role']) ?>">
                        <?= e(ucfirst(str_replace('_',' ',$ticket['user_role']))) ?>
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Phone</span>
                <span class="info-value"><?= e($ticket['user_phone'] ?: '—') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Submitted</span>
                <span class="info-value">
                    <?= e(date('F d, Y g:i A', strtotime($ticket['created_at']))) ?>
                </span>
            </div>
        </div>
    <?php else: ?>
        <p class="empty-state">Anonymous submission (user account deleted)</p>
    <?php endif; ?>
</div>

<!-- Ticket Subject & Message -->
<div class="card">
    <h3>📩 Subject</h3>
    <p class="ticket-subject"><?= e($ticket['subject'] ?? '— no subject —') ?></p>

    <h3 style="margin-top:20px;">💬 Message</h3>
    <div class="ticket-message">
        <?= nl2br(e($ticket['message'] ?? '— no message —')) ?>
    </div>
</div>

<!-- Admin Notes Form -->
<div class="card">
    <h3>📝 Admin Notes</h3>
    <p class="form-intro">
        Notes are visible only to admins. Use this space to track internal investigation,
        responses, or resolution steps.
    </p>

    <form method="POST" action="<?= BASE_URL ?>support_tickets/details/<?= e($ticket['id']) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <textarea name="admin_notes"
                      rows="6"
                      placeholder="Add your notes about this ticket..."
                      maxlength="5000"><?= e($ticket['admin_notes'] ?? '') ?></textarea>
            <small class="form-hint">Max 5000 characters. Saved when you click "Save Notes".</small>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>support_tickets" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                💾 Save Notes
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>