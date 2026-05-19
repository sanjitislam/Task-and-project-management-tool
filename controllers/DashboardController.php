<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard with platform statistics.
     */
    public function index()
    {
        // RBAC — only admins can see this page
        Auth::requireRole('admin');

        // Load Models
        $userModel      = $this->model('UserModel');
        $workspaceModel = $this->model('WorkspaceModel');
        $projectModel   = $this->model('ProjectModel');
        $taskModel      = $this->model('TaskModel');

        // Fetch stats
        $stats = [
            'total_workspaces' => $workspaceModel->countAll(),
            'total_users'      => $userModel->countAll(),
            'users_by_role'    => $userModel->countByRole(),
            'active_projects'  => $projectModel->countActive(),
            'tasks_today'      => $taskModel->countCreatedToday(),
        ];

        // Pass data to view
        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'stats'     => $stats
        ]);
    }
}

?>