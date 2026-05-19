<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>⚙️ Platform Settings</h2>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>settings" class="btn btn-primary btn-small active-tab">
            🔢 Plan Limits
        </a>
        <a href="<?= BASE_URL ?>settings/announcements" class="btn btn-secondary btn-small">
            📢 Announcements
        </a>
        <a href="<?= BASE_URL ?>settings/reports" class="btn btn-secondary btn-small">
            📥 Reports
        </a>
    </div>
</div>

<div class="card form-card">
    <h3>🔢 Plan Limits</h3>
    <p class="form-intro">
        Set the maximum number of projects allowed per workspace plan.
        These limits take effect immediately.
    </p>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>settings">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="free_max">
                    Free Plan — Max Projects <span class="required">*</span>
                </label>
                <input type="number" id="free_max" name="free_max"
                       min="1" max="1000"
                       value="<?= e($plans['free']['max_projects'] ?? 5) ?>"
                       required>
                <?php if (!empty($errors['free_max'])): ?>
                    <span class="error"><?= e($errors['free_max']) ?></span>
                <?php endif; ?>
                <small class="form-hint">Currently affects all free-tier workspaces.</small>
            </div>

            <div class="form-group">
                <label for="pro_max">
                    Pro Plan — Max Projects <span class="required">*</span>
                </label>
                <input type="number" id="pro_max" name="pro_max"
                       min="1" max="10000"
                       value="<?= e($plans['pro']['max_projects'] ?? 100) ?>"
                       required>
                <?php if (!empty($errors['pro_max'])): ?>
                    <span class="error"><?= e($errors['pro_max']) ?></span>
                <?php endif; ?>
                <small class="form-hint">Should be ≥ Free plan limit.</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                💾 Save Plan Limits
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>