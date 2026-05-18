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
}


?>