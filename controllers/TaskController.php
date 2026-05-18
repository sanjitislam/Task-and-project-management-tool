<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class TaskController extends Controller
{
    /**
     * GET /tasks
     * List all tasks with filters.
     */
    public function index()
    {
        Auth::requireRole('admin');

        $filters = [
            'status'      => $_GET['status']      ?? '',
            'priority'    => $_GET['priority']    ?? '',
            'assignee_id' => $_GET['assignee_id'] ?? '',
            'project_id'  => $_GET['project_id']  ?? ''
        ];

        $taskModel = $this->model('TaskModel');

        $tasks     = $taskModel->getAll($filters);
        $assignees = $taskModel->getAllAssignees();
        $projects  = $taskModel->getAllProjectsSimple();

        $this->view('tasks/index', [
            'pageTitle' => 'Tasks',
            'tasks'     => $tasks,
            'assignees' => $assignees,
            'projects'  => $projects,
            'filters'   => $filters
        ]);
    }

    /**
     * GET /tasks/details/{id}
     * Show full task details.
     */
    public function details($id = null)
    {
        Auth::requireRole('admin');

        $id = (int)$id;
        if ($id <= 0) {
            set_flash('error', 'Invalid task ID.');
            redirect('tasks');
        }

        $taskModel = $this->model('TaskModel');
        $task = $taskModel->getDetails($id);

        if (!$task) {
            set_flash('error', 'Task not found.');
            redirect('tasks');
        }

        $this->view('tasks/view', [
            'pageTitle' => 'Task: ' . $task['title'],
            'task'      => $task
        ]);
    }
}
?>