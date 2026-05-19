<?php
define('APP_RUNNING', true);
define('BASE_URL', '/task_management/');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<h2>Step 10 Diagnostic</h2>";

echo "<h3>1. Loading core files...</h3>";

try {
    require_once __DIR__ . '/core/Database.php';
    echo "✅ Database.php loaded<br>";
    
    require_once __DIR__ . '/core/Model.php';
    echo "✅ Model.php loaded<br>";
    
    require_once __DIR__ . '/core/Controller.php';
    echo "✅ Controller.php loaded<br>";
    
    require_once __DIR__ . '/core/Auth.php';
    echo "✅ Auth.php loaded<br>";
    
    require_once __DIR__ . '/core/ActivityLogger.php';
    echo "✅ ActivityLogger.php loaded<br>";
    
    require_once __DIR__ . '/helpers/functions.php';
    echo "✅ functions.php loaded<br>";
    
    require_once __DIR__ . '/helpers/validation.php';
    echo "✅ validation.php loaded<br>";
    
    require_once __DIR__ . '/models/ActivityLogModel.php';
    echo "✅ ActivityLogModel.php loaded<br>";
    
    require_once __DIR__ . '/controllers/ActivityLogController.php';
    echo "✅ ActivityLogController.php loaded<br>";

    echo "<h3>2. Testing classes exist...</h3>";
    echo class_exists('ActivityLogger') ? '✅ ActivityLogger class exists<br>' : '❌ ActivityLogger MISSING<br>';
    echo class_exists('ActivityLogModel') ? '✅ ActivityLogModel class exists<br>' : '❌ ActivityLogModel MISSING<br>';
    echo class_exists('ActivityLogController') ? '✅ ActivityLogController class exists<br>' : '❌ ActivityLogController MISSING<br>';

    echo "<h3>3. Testing database query...</h3>";
    $model = new ActivityLogModel();
    $logs = $model->getAll();
    echo "✅ getAll() returned " . count($logs) . " logs<br>";

    echo "<h3>4. ActivityLogger label test:</h3>";
    echo ActivityLogger::actionLabel('user_login') . "<br>";

    echo "<h3 style='color:green'>✅ All systems working!</h3>";
    echo "<p>The problem must be in <code>index.php</code> routing.</p>";
    echo "<p>Visit: <a href='" . BASE_URL . "activity_logs'>" . BASE_URL . "activity_logs</a> and tell me the error.</p>";

} catch (Throwable $e) {
    echo "<h3 style='color:red'>❌ ERROR FOUND:</h3>";
    echo "<pre style='background:#fee; padding:15px;'>";
    echo "Message: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}