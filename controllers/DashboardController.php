<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class DashboardController extends Controller
{
    public function index()
    {
        // RBAC — only admins past this point
        Auth::requireRole('admin');

        $user = Auth::user();

        // Temporary placeholder until Step 4
        echo "<div style='padding:40px;font-family:sans-serif;'>";
        echo "<h1>✅ Login successful!</h1>";
        echo "<p>Welcome, <strong>" . e($user['name']) . "</strong> (" . e($user['email']) . ")</p>";
        echo "<p>Role: <strong>" . e($user['role']) . "</strong></p>";
        echo "<p><a href='" . BASE_URL . "logout'>Logout</a></p>";
        echo "</div>";
    }
}

?>