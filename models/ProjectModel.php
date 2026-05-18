<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class ProjectModel extends Model
{
    /**
     * Count all projects in the system.
     */
    public function countAll()
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM projects");
        return (int)($row['total'] ?? 0);
    }

    /**
     * Count projects with status = 'active'.
     */
    public function countActive()
    {
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS total FROM projects WHERE status = ?",
            "s",
            ['active']
        );
        return (int)($row['total'] ?? 0);
    }
}

?>