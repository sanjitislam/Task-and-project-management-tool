<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class WorkspaceController extends Controller
{
    /**
     * GET /workspaces
     * Display list of all workspaces with search support.
     */
    public function index()
    {
        Auth::requireRole('admin');

        $search = trim($_GET['search'] ?? '');

        $workspaceModel = $this->model('WorkspaceModel');
        $workspaces = $workspaceModel->getAll($search);

        $this->view('workspaces/index', [
            'pageTitle'  => 'Workspaces',
            'workspaces' => $workspaces,
            'search'     => $search
        ]);
    }

    /**
     * GET /workspaces/delete/{id}
     * Delete a workspace permanently.
     */
    public function delete($id = null)
    {
        Auth::requireRole('admin');

        $id = (int)$id;
        if ($id <= 0) {
            set_flash('error', 'Invalid workspace ID.');
            redirect('workspaces');
        }

        // Verify CSRF token (sent as query param from confirmed link)
        if (!verify_csrf($_GET['csrf_token'] ?? '')) {
            set_flash('error', 'Security token invalid. Please try again.');
            redirect('workspaces');
        }

        $workspaceModel = $this->model('WorkspaceModel');
        $workspace = $workspaceModel->findById($id);

        if (!$workspace) {
            set_flash('error', 'Workspace not found.');
            redirect('workspaces');
        }

        if ($workspaceModel->delete($id)) {
            set_flash('success', "Workspace \"{$workspace['name']}\" deleted successfully.");
        } else {
            set_flash('error', 'Failed to delete workspace. It may have related data.');
        }

        redirect('workspaces');
    }

    /**
     * GET /workspaces/members/{id}
     * Show members of a specific workspace.
     */
    public function members($id = null)
    {
        Auth::requireRole('admin');

        $id = (int)$id;
        if ($id <= 0) {
            set_flash('error', 'Invalid workspace ID.');
            redirect('workspaces');
        }

        $workspaceModel = $this->model('WorkspaceModel');
        $workspace = $workspaceModel->findById($id);

        if (!$workspace) {
            set_flash('error', 'Workspace not found.');
            redirect('workspaces');
        }

        $members = $workspaceModel->getMembers($id);

        $this->view('workspaces/members', [
            'pageTitle' => 'Workspace Members',
            'workspace' => $workspace,
            'members'   => $members
        ]);
    }

    /**
     * GET /workspaces/removeMember/{workspaceId}/{userId}
     * Remove a member from a workspace.
     */
    public function removeMember($workspaceId = null, $userId = null)
    {
        Auth::requireRole('admin');

        $workspaceId = (int)$workspaceId;
        $userId = (int)$userId;

        if ($workspaceId <= 0 || $userId <= 0) {
            set_flash('error', 'Invalid parameters.');
            redirect('workspaces');
        }

        if (!verify_csrf($_GET['csrf_token'] ?? '')) {
            set_flash('error', 'Security token invalid.');
            redirect('workspaces/members/' . $workspaceId);
        }

        $workspaceModel = $this->model('WorkspaceModel');

        if ($workspaceModel->removeMember($workspaceId, $userId)) {
            set_flash('success', 'Member removed from workspace.');
        } else {
            set_flash('error', 'Failed to remove member.');
        }

        redirect('workspaces/members/' . $workspaceId);
    }
}


?>