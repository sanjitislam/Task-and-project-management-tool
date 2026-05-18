<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>📨 Invite User to Workspace</h2>
    <a href="<?= BASE_URL ?>users" class="btn btn-secondary btn-small">
        ← Back to Users
    </a>
</div>

<!-- Show temp password if a new user was just created -->
<?php if (!empty($inviteResult)): ?>
    <div class="card success-card">
        <h3>🎉 Invitation Successful!</h3>
        <p>A new user account has been created:</p>

        <table class="invite-result">
            <tr>
                <td><strong>Email:</strong></td>
                <td><?= e($inviteResult['email']) ?></td>
            </tr>
            <tr>
                <td><strong>Workspace:</strong></td>
                <td><?= e($inviteResult['workspace']) ?></td>
            </tr>
            <tr>
                <td><strong>Role:</strong></td>
                <td><?= e(ucfirst($inviteResult['role'])) ?></td>
            </tr>
            <tr>
                <td><strong>Temporary password:</strong></td>
                <td>
                    <code class="temp-password"><?= e($inviteResult['temp_password']) ?></code>
                </td>
            </tr>
        </table>

        <div class="alert alert-warning" style="margin-top:14px;">
            ⚠️ <strong>Important:</strong> This password is shown only once.
            Share it securely with the user. They should change it after first login.
        </div>

        <div class="form-actions" style="margin-top:14px;">
            <a href="<?= BASE_URL ?>users" class="btn btn-secondary">Done</a>
            <a href="<?= BASE_URL ?>users/invite" class="btn btn-primary">Invite Another User</a>
        </div>
    </div>
<?php else: ?>

<div class="card form-card">

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <p class="form-intro">
        Invite a user to a workspace. If the email is not registered yet,
        a new account will be created automatically with a temporary password.
    </p>

    <form method="POST" action="<?= BASE_URL ?>users/invite" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="workspace_id">Workspace <span class="required">*</span></label>
            <select id="workspace_id" name="workspace_id" required>
                <option value="">-- Select a workspace --</option>
                <?php $workspaces = $workspaces ?? []; ?>
                <?php foreach ($workspaces as $w): ?>
                    <option value="<?= e($w['id']) ?>"
                            <?= old('workspace_id') == $w['id'] ? 'selected' : '' ?>>
                        <?= e($w['name']) ?>
                        (<?= e(ucfirst($w['plan'])) ?><?= $w['is_active'] ? '' : ' — INACTIVE' ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['workspace_id'])): ?>
                <span class="error"><?= e($errors['workspace_id']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email"
                   value="<?= e(old('email')) ?>"
                   placeholder="user@example.com"
                   required>
            <?php if (!empty($errors['email'])): ?>
                <span class="error"><?= e($errors['email']) ?></span>
            <?php endif; ?>
            <small class="form-hint">
                If this email doesn't exist, a new account will be created.
            </small>
        </div>

        <div class="form-group">
            <label for="workspace_role">Role in Workspace <span class="required">*</span></label>
            <select id="workspace_role" name="workspace_role" required>
                <option value="">-- Select a role --</option>
                <option value="member" <?= old('workspace_role')==='member' ?'selected':'' ?>>
                    Member (regular contributor)
                </option>
                <option value="lead"   <?= old('workspace_role')==='lead'   ?'selected':'' ?>>
                    Team Lead (manage projects & tasks)
                </option>
                <option value="client" <?= old('workspace_role')==='client' ?'selected':'' ?>>
                    Client (read-only access)
                </option>
                <option value="admin"  <?= old('workspace_role')==='admin'  ?'selected':'' ?>>
                    Workspace Admin
                </option>
            </select>
            <?php if (!empty($errors['workspace_role'])): ?>
                <span class="error"><?= e($errors['workspace_role']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>users" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                📨 Send Invitation
            </button>
        </div>
    </form>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>