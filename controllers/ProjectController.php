<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class ProjectController extends Controller
{
    public function index()
    {
        Auth::requireRole('admin');

        $filters = [
            'workspace_id' => $_GET['workspace_id'] ?? '',
            'status'       => $_GET['status']       ?? '',
            'team_lead_id' => $_GET['team_lead_id'] ?? ''
        ];

        $projectModel   = $this->model('ProjectModel');
        $workspaceModel = $this->model('WorkspaceModel');

        $projects   = $projectModel->getAll($filters);
        $workspaces = $workspaceModel->getAllSimple();
        $teamLeads  = $projectModel->getAllTeamLeads();

        $this->view('projects/index', [
            'pageTitle'  => 'Projects',
            'projects'   => $projects,
            'workspaces' => $workspaces,
            'teamLeads'  => $teamLeads,
            'filters'    => $filters
        ]);
    }

    public function details($id = null)
    {
        Auth::requireRole('admin');

        $id = (int)$id;
        if ($id <= 0) {
            set_flash('error', 'Invalid project ID.');
            redirect('projects');
        }

        $projectModel = $this->model('ProjectModel');
        $project = $projectModel->getDetails($id);

        if (!$project) {
            set_flash('error', 'Project not found.');
            redirect('projects');
        }

        $this->view('projects/view', [
            'pageTitle' => 'Project: ' . $project['name'],
            'project'   => $project
        ]);
    }
}
?>