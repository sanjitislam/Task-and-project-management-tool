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

    /**
     * GET  /users/createAdmin  — show form
     * POST /users/createAdmin  — process form
     */
    public function createAdmin()
    {
        Auth::requireRole('admin');

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // ---- CSRF ----
            if (!verify_csrf($_POST['csrf_token'] ?? '')) {
                set_flash('error', 'Invalid form submission. Please try again.');
                redirect('users/createAdmin');
            }

            // ---- Validate ----
            $errors = validate($_POST, [
                'name'             => 'required|min:2|max:100',
                'email'            => 'required|email|max:100',
                'phone'            => 'max:20',
                'password'         => 'required|min:6|max:100',
                'password_confirm' => 'required|matches:password'
            ]);

            // ---- Check email uniqueness ----
            if (empty($errors['email'])) {
                $userModel = $this->model('UserModel');
                if ($userModel->emailExists(trim($_POST['email']))) {
                    $errors['email'] = 'This email is already registered.';
                }
            }

            // ---- If no errors, create the admin ----
            if (empty($errors)) {
                $userModel = $this->model('UserModel');

                $newId = $userModel->create([
                    'name'     => trim($_POST['name']),
                    'email'    => trim($_POST['email']),
                    'phone'    => trim($_POST['phone'] ?? ''),
                    'password' => $_POST['password'],
                    'role'     => 'admin'
                ]);

                if ($newId) {
                     ActivityLogger::log(
                    'admin_created',
                     'New admin created: ' . $_POST['name'] . ' (' . $_POST['email'] . ')'
                     );
                     set_flash('success',
                    'Admin account "' . htmlspecialchars($_POST['name']) . '" created successfully!'
                       );
                     clear_old();
                      redirect('users');
                } else {
                    $errors['general'] = 'Failed to create admin account. Please try again.';
                }
            }

            // Validation failed → keep old input
            flash_old($_POST);
        }

        $this->view('users/create_admin', [
            'pageTitle' => 'Create Admin',
            'errors'    => $errors
        ]);

        clear_old();
    }

    /**
     * GET  /users/invite  — show form
     * POST /users/invite  — process invitation
     */
    public function invite()
    {
        Auth::requireRole('admin');

        $errors = [];
        $tempPassword = null;
        $inviteResult = null;

        $workspaceModel = $this->model('WorkspaceModel');
        $workspaces = $workspaceModel->getAllSimple();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // ---- CSRF ----
            if (!verify_csrf($_POST['csrf_token'] ?? '')) {
                set_flash('error', 'Invalid form submission.');
                redirect('users/invite');
            }

            // ---- Validate ----
            $errors = validate($_POST, [
                'workspace_id'   => 'required|numeric',
                'email'          => 'required|email|max:100',
                'workspace_role' => 'required|in:member,lead,client,admin'
            ]);

            // ---- Check workspace exists ----
            if (empty($errors['workspace_id'])) {
                $workspace = $workspaceModel->findById((int)$_POST['workspace_id']);
                if (!$workspace) {
                    $errors['workspace_id'] = 'Selected workspace does not exist.';
                }
            }

            // ---- If no errors, process the invite ----
            if (empty($errors)) {
                $userModel = $this->model('UserModel');
                $email = trim($_POST['email']);
                $workspaceId = (int)$_POST['workspace_id'];
                $wsRole = trim($_POST['workspace_role']);

                // Does the user already exist?
                $existingUser = $userModel->findByEmail($email);

                if ($existingUser) {
                    // User exists — just add to workspace if not already a member
                    if ($workspaceModel->isMember($workspaceId, $existingUser['id'])) {
                        $errors['email'] = 'This user is already a member of this workspace.';
                        flash_old($_POST);
                    } else {
                        if ($workspaceModel->addMember($workspaceId, $existingUser['id'], $wsRole)) {
                            set_flash('success',
                                'Added existing user "' . htmlspecialchars($existingUser['name']) .
                                '" to workspace "' . htmlspecialchars($workspace['name']) . '".'
                            );
                            clear_old();
                            redirect('users');
                        } else {
                            $errors['general'] = 'Failed to add user to workspace.';
                        }
                    }
                } else {
                    // User doesn't exist — create new user with temp password
                    $tempPassword = generate_random_password(10);

                    // Map workspace role to platform role (best guess)
                    $platformRole = ($wsRole === 'lead')   ? 'team_lead'
                                  : (($wsRole === 'admin')  ? 'admin'
                                  : (($wsRole === 'client') ? 'client' : 'member'));

                    $newUserId = $userModel->create([
                        'name'     => trim(strstr($email, '@', true)),  // use part before @ as name
                        'email'    => $email,
                        'phone'    => '',
                        'password' => $tempPassword,
                        'role'     => $platformRole
                    ]);

                    if ($newUserId) {
                        $workspaceModel->addMember($workspaceId, $newUserId, $wsRole);
                         ActivityLogger::log(
                         'user_invited',
                         'New user invited: ' . $email . ' to workspace "' . $workspace['name'] . '" as ' . $wsRole,
                         $workspaceId
                          );
                        // We show the temp password ONCE on the form result page
                        $inviteResult = [
                            'email'        => $email,
                            'temp_password'=> $tempPassword,
                            'workspace'    => $workspace['name'],
                            'role'         => $wsRole
                        ];

                        clear_old();
                    } else {
                        $errors['general'] = 'Failed to create user account.';
                        flash_old($_POST);
                    }
                }
            } else {
                flash_old($_POST);
            }
        }

        $this->view('users/invite', [
            'pageTitle'    => 'Invite User',
            'errors'       => $errors,
            'workspaces'   => $workspaces,
            'inviteResult' => $inviteResult
        ]);

        clear_old();
    }
}
?>