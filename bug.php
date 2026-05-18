<?php
define('APP_RUNNING', true);

echo "<h2>File existence check:</h2>";

$files = [
    'core/Database.php',
    'core/Model.php',
    'core/Controller.php',
    'core/Auth.php',
    'helpers/functions.php',
    'helpers/validation.php'
];

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (file_exists($path)) {
        echo "✅ EXISTS: $f<br>";
    } else {
        echo "❌ MISSING: $f<br>";
    }
}

echo "<h2>Loading validation.php and checking validate() function:</h2>";

if (file_exists(__DIR__ . '/helpers/validation.php')) {
    require_once __DIR__ . '/helpers/validation.php';
    
    if (function_exists('validate')) {
        echo "✅ validate() function is available!";
    } else {
        echo "❌ validate() function NOT defined in validation.php";
    }
} else {
    echo "❌ Cannot load validation.php — file missing";
}

?>