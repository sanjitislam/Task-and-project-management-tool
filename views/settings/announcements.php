<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<?php $announcements = $announcements ?? []; ?>

<div class="page-header">
    <h2>⚙️ Platform Settings</h2>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>settings" class="btn btn-secondary btn-small">
            🔢 Plan Limits
        </a>
        <a href="<?= BASE_URL ?>settings/announcements" class="btn btn-primary btn-small active-tab">
            📢 Announcements
        </a>
        <a href="<?= BASE_URL ?>settings/reports" class="btn btn-secondary btn-small">
            📥 Reports
        </a>
    </div>
</div>

<!-- New announcement form -->
<div class="card form-card">
    <h3>📢 Post New Announcement</h3>
    <p class="form-intro">
        Active announcements appear on all user dashboards across the platform.
    </p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>settings/announcements">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="title">Title <span class="required">*</span></label>
            <input type="text" id="title" name="title"
                   value="<?= e(old('title')) ?>"
                   placeholder="e.g. Scheduled maintenance on Sunday"
                   maxlength="200">
            <?php if (!empty($errors['title'])): ?>
                <span class="error"><?= e($errors['title']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="message">Message <span class="required">*</span></label>
            <textarea id="message" name="message" rows="4"
                      maxlength="2000"
                      placeholder="Write your announcement here..."><?= e(old('message')) ?></textarea>
            <?php if (!empty($errors['message'])): ?>
                <span class="error"><?= e($errors['message']) ?></span>
            <?php endif; ?>
            <small class="form-hint">Max 2000 characters.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                📤 Post Announcement
            </button>
        </div>
    </form>
</div>

<!-- Existing announcements -->
<div class="card">
    <h3>📋 All Announcements (<?= count($announcements) ?>)</h3>

    <?php if (empty($announcements)): ?>
        <p class="empty-state">No announcements yet. Post your first one above!</p>
    <?php else: ?>
        <div class="announcement-list">
            <?php foreach ($announcements as $a): ?>
                <div class="announcement-item <?= $a['is_active'] ? '' : 'inactive' ?>">
                    <div class="announcement-header">
                        <h4><?= e($a['title']) ?></h4>
                        <div class="announcement-actions">
                            <button class="toggle-announcement <?= $a['is_active'] ? 'active' : 'inactive' ?>"
                                    data-id="<?= e($a['id']) ?>">
                                <?= $a['is_active'] ? '✅ Active' : '⛔ Inactive' ?>
                            </button>

                            <a href="<?= BASE_URL ?>settings/deleteAnnouncement/<?= e($a['id']) ?>?csrf_token=<?= e(csrf_token()) ?>"
                               class="btn btn-danger btn-small confirm-delete"
                               data-name="<?= e($a['title']) ?>">
                                🗑️ Delete
                            </a>
                        </div>
                    </div>
                    <div class="announcement-body">
                        <?= nl2br(e($a['message'])) ?>
                    </div>
                    <div class="announcement-meta">
                        Posted by <strong><?= e($a['posted_by_name'] ?? 'System') ?></strong>
                        on <?= e(date('F d, Y g:i A', strtotime($a['created_at']))) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>