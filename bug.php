<?php
define('APP_RUNNING', true);

echo "<h2>Step 4 File Check:</h2>";

$files = [
    'controllers/DashboardController.php',
    'models/UserModel.php',
    'models/WorkspaceModel.php',
    'models/ProjectModel.php',
    'models/TaskModel.php',
    'views/layouts/header.php',
    'views/layouts/sidebar.php',
    'views/layouts/footer.php',
    'views/dashboard/index.php',
    'public/css/style.css',
    'public/js/app.js',
    'api/dashboard_stats.php'
];

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✅ EXISTS ({$size} bytes): $f<br>";
    } else {
        echo "❌ MISSING: $f<br>";
    }
}

?>