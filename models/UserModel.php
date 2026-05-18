<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class UserModel extends Model
{
    /* ============ AUTH METHODS (from Step 3) ============ */

    public function findByEmail($email)
    {
        return $this->db->selectOne(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            "s",
            [$email]
        );
    }

    public function findById($id)
    {
        return $this->db->selectOne(
            "SELECT * FROM users WHERE id = ? LIMIT 1",
            "i",
            [$id]
        );
    }

    public function verifyLogin($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) return false;
        if (!$user['is_active']) return 'inactive';
        if (!password_verify($password, $user['password_hash'])) return false;

        return $user;
    }

    /* ============ STAT METHODS (from Step 4) ============ */

    public function countAll()
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM users");
        return (int)($row['total'] ?? 0);
    }

    public function countByRole()
    {
        $rows = $this->db->select(
            "SELECT role, COUNT(*) AS total FROM users GROUP BY role"
        );

        $counts = [
            'admin'     => 0,
            'team_lead' => 0,
            'member'    => 0,
            'client'    => 0
        ];

        foreach ($rows as $row) {
            $counts[$row['role']] = (int)$row['total'];
        }

        return $counts;
    }

    /* ============ NEW: MANAGEMENT METHODS ============ */

    /**
     * Get all users with their workspace count.
     * Optional: filter by search keyword and/or role.
     */
    public function getAll($search = '', $role = '')
    {
        $sql = "
            SELECT 
                u.id, u.name, u.email, u.phone, u.role, 
                u.is_active, u.created_at,
                (SELECT COUNT(*) FROM workspace_members wm 
                 WHERE wm.user_id = u.id) AS workspace_count
            FROM users u
            WHERE 1=1
        ";

        $types = '';
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ?) ";
            $like = '%' . $search . '%';
            $types .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($role)) {
            $sql .= " AND u.role = ? ";
            $types .= 's';
            $params[] = $role;
        }

        $sql .= " ORDER BY u.created_at DESC";

        if (empty($params)) {
            return $this->db->select($sql);
        }
        return $this->db->select($sql, $types, $params);
    }

    /**
     * Get a user's detailed profile with workspace memberships.
     */
    public function getProfile($id)
    {
        $user = $this->findById($id);
        if (!$user) return null;

        // Get workspaces this user belongs to
        $workspaces = $this->db->select(
            "SELECT 
                w.id, w.name, w.plan, w.is_active,
                wm.workspace_role, wm.joined_at
             FROM workspace_members wm
             INNER JOIN workspaces w ON wm.workspace_id = w.id
             WHERE wm.user_id = ?
             ORDER BY wm.joined_at DESC",
            "i",
            [$id]
        );

        // Count tasks assigned to this user
        $taskCount = $this->db->selectOne(
            "SELECT COUNT(*) AS total FROM tasks WHERE assigned_to = ?",
            "i",
            [$id]
        );

        // Count projects this user is a member of
        $projectCount = $this->db->selectOne(
            "SELECT COUNT(*) AS total FROM project_members WHERE user_id = ?",
            "i",
            [$id]
        );

        $user['workspaces'] = $workspaces;
        $user['task_count'] = (int)($taskCount['total'] ?? 0);
        $user['project_count'] = (int)($projectCount['total'] ?? 0);

        return $user;
    }

    /**
     * Toggle active/inactive.
     */
    public function toggleActive($id)
    {
        return $this->db->execute(
            "UPDATE users SET is_active = NOT is_active WHERE id = ?",
            "i",
            [$id]
        );
    }

    public function getActiveStatus($id)
    {
        $row = $this->db->selectOne(
            "SELECT is_active FROM users WHERE id = ?",
            "i",
            [$id]
        );
        return $row ? (int)$row['is_active'] : null;
    }

    /**
     * Change user's role.
     * Allowed roles: member, team_lead, client, admin
     */
    public function changeRole($id, $newRole)
    {
        $allowed = ['member', 'team_lead', 'client', 'admin'];
        if (!in_array($newRole, $allowed)) {
            return false;
        }

        return $this->db->execute(
            "UPDATE users SET role = ? WHERE id = ?",
            "si",
            [$newRole, $id]
        );
    }
}

?>