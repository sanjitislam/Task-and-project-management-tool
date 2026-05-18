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
            // Delete in correct order (children before parents)
            $this->db->execute("DELETE FROM workspace_members WHERE workspace_id = ?", "i", [$id]);
            $this->db->execute("DELETE FROM activity_logs WHERE workspace_id = ?", "i", [$id]);

            // Delete the workspace itself
            $this->db->execute("DELETE FROM workspaces WHERE id = ?", "i", [$id]);

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
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
    
}


?>