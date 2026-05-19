<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class WorkspaceModel extends Model
{
    /**
     * Count total workspaces in the system.
     */
    public function countAll()
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM workspaces");
        return (int)($row['total'] ?? 0);
    }

    /**
     * Count only active workspaces.
     */
    public function countActive()
    {
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS total FROM workspaces WHERE is_active = 1"
        );
        return (int)($row['total'] ?? 0);
    }


    /**
     * Get all workspaces with owner name and member count.
     * Optional: filter by search keyword.
     */

    public function getAll($search = '')
    {
        $sql = "
            SELECT 
                w.id, w.name, w.description, w.invite_code, 
                w.plan, w.is_active, w.created_at,
                u.name AS owner_name, u.email AS owner_email,
                (SELECT COUNT(*) FROM workspace_members wm 
                 WHERE wm.workspace_id = w.id) AS member_count,
                (SELECT COUNT(*) FROM projects p 
                 WHERE p.workspace_id = w.id) AS project_count
            FROM workspaces w
            LEFT JOIN users u ON w.owner_id = u.id
        ";

        if (!empty($search)) {
            $sql .= " WHERE w.name LIKE ? OR w.description LIKE ? ";
            $like = '%' . $search . '%';
            $sql .= " ORDER BY w.created_at DESC";
            return $this->db->select($sql, "ss", [$like, $like]);
        }

        $sql .= " ORDER BY w.created_at DESC";
        return $this->db->select($sql);
    }

    /**
     * Find a single workspace by ID with owner info.
     */
    public function findById($id)
    {
        return $this->db->selectOne(
            "SELECT w.*, u.name AS owner_name, u.email AS owner_email
             FROM workspaces w
             LEFT JOIN users u ON w.owner_id = u.id
             WHERE w.id = ?",
            "i",
            [$id]
        );
    }

     /**
     * Toggle active/inactive status.
     */
    public function toggleActive($id)
    {
        return $this->db->execute(
            "UPDATE workspaces SET is_active = NOT is_active WHERE id = ?",
            "i",
            [$id]
        );
    }

     /**
     * Get current active status (used after toggle for AJAX response).
     */
    public function getActiveStatus($id)
    {
        $row = $this->db->selectOne(
            "SELECT is_active FROM workspaces WHERE id = ?",
            "i",
            [$id]
        );
        return $row ? (int)$row['is_active'] : null;
    }
   

    /**
     * Delete a workspace.
     * NOTE: Must delete related records first to satisfy foreign keys.
     */

    public function delete($id)
    {
        $conn = $this->db->getConnection();
        $conn->begin_transaction();

        try {
            $id = (int)$id;

            // Remove logs connected to this workspace or any project inside it.
            $this->db->execute(
                "DELETE al FROM activity_logs al
                 LEFT JOIN projects p ON al.project_id = p.id
                 WHERE al.workspace_id = ? OR p.workspace_id = ?",
                "ii",
                [$id, $id]
            );

            // Remove child records for tasks inside workspace projects.
            $this->db->execute(
                "DELETE ta FROM task_attachments ta
                 INNER JOIN tasks t ON ta.task_id = t.id
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = ?",
                "i",
                [$id]
            );

            $this->db->execute(
                "DELETE cm FROM comments cm
                 INNER JOIN tasks t ON cm.task_id = t.id
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = ?",
                "i",
                [$id]
            );

            $this->db->execute(
                "DELETE tl FROM time_logs tl
                 INNER JOIN tasks t ON tl.task_id = t.id
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = ?",
                "i",
                [$id]
            );

            // Remove client feedback connected to milestones in workspace projects.
            $this->db->execute(
                "DELETE cf FROM client_feedback cf
                 INNER JOIN milestones m ON cf.milestone_id = m.id
                 INNER JOIN projects p ON m.project_id = p.id
                 WHERE p.workspace_id = ?",
                "i",
                [$id]
            );

            // Remove tasks before milestones/projects because of foreign keys.
            $this->db->execute(
                "DELETE t FROM tasks t
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = ?",
                "i",
                [$id]
            );

            $this->db->execute(
                "DELETE m FROM milestones m
                 INNER JOIN projects p ON m.project_id = p.id
                 WHERE p.workspace_id = ?",
                "i",
                [$id]
            );

            $this->db->execute(
                "DELETE pm FROM project_members pm
                 INNER JOIN projects p ON pm.project_id = p.id
                 WHERE p.workspace_id = ?",
                "i",
                [$id]
            );

            $this->db->execute("DELETE FROM projects WHERE workspace_id = ?", "i", [$id]);
            $this->db->execute("DELETE FROM workspace_members WHERE workspace_id = ?", "i", [$id]);
            $this->db->execute("DELETE FROM workspaces WHERE id = ?", "i", [$id]);

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            error_log('Workspace delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all members of a workspace (with user info).
     */

    public function getMembers($workspaceId)
    {
        return $this->db->select(
            "SELECT 
                wm.id AS member_id, wm.workspace_role, wm.joined_at,
                u.id AS user_id, u.name, u.email, u.role AS global_role, u.is_active
             FROM workspace_members wm
             INNER JOIN users u ON wm.user_id = u.id
             WHERE wm.workspace_id = ?
             ORDER BY wm.joined_at DESC",
            "i",
            [$workspaceId]
        );
    }
    
     /**
     * Remove a specific member from a workspace.
     */
     public function removeMember($workspaceId, $userId)
    {
        return $this->db->execute(
            "DELETE FROM workspace_members WHERE workspace_id = ? AND user_id = ?",
            "ii",
            [$workspaceId, $userId]
        );
    }

    /* ============ STEP 7: MEMBERSHIP METHODS ============ */

    /**
     * Get all workspaces (simple list — id and name only).
     * Used in dropdown of invite form.
     */
    public function getAllSimple()
    {
        return $this->db->select(
            "SELECT id, name, plan, is_active 
             FROM workspaces 
             ORDER BY name ASC"
        );
    }

    /**
     * Check if a user is already a member of a workspace.
     */
    public function isMember($workspaceId, $userId)
    {
        $row = $this->db->selectOne(
            "SELECT id FROM workspace_members 
             WHERE workspace_id = ? AND user_id = ? LIMIT 1",
            "ii",
            [$workspaceId, $userId]
        );
        return $row !== null;
    }

    /**
     * Add a user to a workspace with a specific role.
     */
    public function addMember($workspaceId, $userId, $workspaceRole)
    {
        $allowed = ['member', 'lead', 'client', 'admin'];
        if (!in_array($workspaceRole, $allowed)) {
            return false;
        }

        return $this->db->execute(
            "INSERT INTO workspace_members 
             (workspace_id, user_id, workspace_role, joined_at)
             VALUES (?, ?, ?, NOW())",
            "iis",
            [$workspaceId, $userId, $workspaceRole]
        );
    }

    
}


?>