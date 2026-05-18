<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class UserController extends Controller
{
    /**
     * GET /users
     * List all users with search + role filter.
     */
    public function index()
    {
        Auth::requireRole('admin');

        $search = trim($_GET['search'] ?? '');
        $role   = trim($_GET['role']   ?? '');

        $userModel = $this->model('UserModel');
        $users = $userModel->getAll($search, $role);

        $this->view('users/index', [
            'pageTitle' => 'Users',
            'users'     => $users,
            'search'    => $search,
            'role'      => $role
        ]);
    }

    /**
     * GET /users/profile/{id}
     * Show user profile details.
     */
    public function profile($id = null)
    {
        Auth::requireRole('admin');

        $id = (int)$id;
        if ($id <= 0) {
            set_flash('error', 'Invalid user ID.');
            redirect('users');
        }

        $userModel = $this->model('UserModel');
        $user = $userModel->getProfile($id);

        if (!$user) {
            set_flash('error', 'User not found.');
            redirect('users');
        }

        $this->view('users/profile', [
            'pageTitle' => 'User Profile',
            'user'      => $user
        ]);
    }
}

?>