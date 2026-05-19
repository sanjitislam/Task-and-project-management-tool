<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php
// Get current URL to highlight active menu item
$currentUrl = $_GET['url'] ?? 'dashboard';
$currentUrl = explode('/', $currentUrl)[0];
?>

<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>dashboard"
           class="nav-item <?= $currentUrl === 'dashboard' ? 'active' : '' ?>">
            📊 <span>Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>workspaces"
           class="nav-item <?= $currentUrl === 'workspaces' ? 'active' : '' ?>">
            🏢 <span>Workspaces</span>
        </a>

        <a href="<?= BASE_URL ?>users"
           class="nav-item <?= $currentUrl === 'users' ? 'active' : '' ?>">
            👥 <span>Users</span>
        </a>

        <a href="<?= BASE_URL ?>projects"
           class="nav-item <?= $currentUrl === 'projects' ? 'active' : '' ?>">
            📁 <span>Projects</span>
        </a>

        <a href="<?= BASE_URL ?>tasks"
           class="nav-item <?= $currentUrl === 'tasks' ? 'active' : '' ?>">
            ✅ <span>Tasks</span>
        </a>

        <a href="<?= BASE_URL ?>activity_logs"
           class="nav-item <?= $currentUrl === 'activity_logs' ? 'active' : '' ?>">
            📜 <span>Activity Logs</span>
        </a>

        <a href="<?= BASE_URL ?>support_tickets"
           class="nav-item <?= $currentUrl === 'support_tickets' ? 'active' : '' ?>">
            🎫 <span>Support Tickets</span>
        </a>

        <a href="<?= BASE_URL ?>analytics"
           class="nav-item <?= $currentUrl === 'analytics' ? 'active' : '' ?>">
            📈 <span>Analytics</span>
        </a>

        <a href="<?= BASE_URL ?>settings"
           class="nav-item <?= $currentUrl === 'settings' ? 'active' : '' ?>">
            ⚙️ <span>Settings</span>
        </a>
    </nav>
</aside>

<main class="main-content">

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>