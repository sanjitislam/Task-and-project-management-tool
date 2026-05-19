<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class ActivityLogController extends Controller
{
    /**
     * GET /activity_logs
     * Display the activity log page with filters.
     */
    public function index()
    {
        Auth::requireRole('admin');

        $filters = [
            'from_date'    => $_GET['from_date']    ?? '',
            'to_date'      => $_GET['to_date']      ?? '',
            'action_type'  => $_GET['action_type']  ?? '',
            'user_id'      => $_GET['user_id']      ?? '',
            'workspace_id' => $_GET['workspace_id'] ?? ''
        ];

        $logModel       = $this->model('ActivityLogModel');
        $workspaceModel = $this->model('WorkspaceModel');

        $logs        = $logModel->getAll($filters);
        $actionTypes = $logModel->getDistinctActionTypes();
        $users       = $logModel->getDistinctUsers();
        $workspaces  = $workspaceModel->getAllSimple();

        $this->view('activity_logs/index', [
            'pageTitle'   => 'Activity Logs',
            'logs'        => $logs,
            'actionTypes' => $actionTypes,
            'users'       => $users,
            'workspaces'  => $workspaces,
            'filters'     => $filters
        ]);
    }
}

?>