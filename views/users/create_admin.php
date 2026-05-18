<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>➕ Create New Admin</h2>
    <a href="<?= BASE_URL ?>users" class="btn btn-secondary btn-small">
        ← Back to Users
    </a>
</div>

<div class="card form-card">

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <p class="form-intro">
        Create a new platform administrator account. This person will have
        full access to all admin features.
    </p>

    <form method="POST" action="<?= BASE_URL ?>users/createAdmin" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="name">Full Name <span class="required">*</span></label>
            <input type="text" id="name" name="name"
                   value="<?= e(old('name')) ?>"
                   placeholder="John Doe"
                   required>
            <?php if (!empty($errors['name'])): ?>
                <span class="error"><?= e($errors['name']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email"
                   value="<?= e(old('email')) ?>"
                   placeholder="john@example.com"
                   required>
            <?php if (!empty($errors['email'])): ?>
                <span class="error"><?= e($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Phone (optional)</label>
            <input type="text" id="phone" name="phone"
                   value="<?= e(old('phone')) ?>"
                   placeholder="+1234567890">
            <?php if (!empty($errors['phone'])): ?>
                <span class="error"><?= e($errors['phone']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password"
                       placeholder="Minimum 6 characters"
                       required>
                <?php if (!empty($errors['password'])): ?>
                    <span class="error"><?= e($errors['password']) ?></span>
                <?php endif; ?>
                <small class="form-hint">At least 6 characters</small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm"
                       placeholder="Re-enter password"
                       required>
                <?php if (!empty($errors['password_confirm'])): ?>
                    <span class="error"><?= e($errors['password_confirm']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>users" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                ✓ Create Admin Account
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>