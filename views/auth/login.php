<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.');
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
// Ensure variables used in the view are defined to avoid undefined variable notices
$email = isset($email) ? $email : '';
$errors = isset($errors) ? $errors : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Task Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-base-url" content="<?= e(BASE_URL) ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>
<body class="auth-body">

    <div class="auth-card">
        <h1>Admin Login</h1>
        <p class="auth-subtitle">Task & Project Management Platform</p>

        <?php if ($flash = get_flash()): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <?= e($errors['general']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>login" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="email" name="email"
                       value="<?= e($email) ?>" autofocus>
                <?php if (!empty($errors['email'])): ?>
                    <span class="error"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <?php if (!empty($errors['password'])): ?>
                    <span class="error"><?= e($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <p class="auth-footer">Admin module — Task Management Tool</p>
    </div>

</body>
</html>
