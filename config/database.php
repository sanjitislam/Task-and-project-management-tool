<?php
/**
 * Database Configuration
 * --------------------------------------
 * Stores all database credentials in ONE place.
 * If credentials change (e.g., when moving to a live server),
 * we only update this file — nothing else.
 */

// Prevent direct access to this file from a browser
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

return [
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',                  
    'database' => 'task_management',
    'charset'  => 'utf8mb4'
];

?>