<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class TaskModel extends Model
{
    /**
     * Count all tasks.
     */
    public function countAll()
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM tasks");
        return (int)($row['total'] ?? 0);
    }

    /**
     * Count tasks created today.
     * Uses DATE() to compare only the date part of created_at.
     */
    public function countCreatedToday()
    {
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS total 
             FROM tasks 
             WHERE DATE(created_at) = CURDATE()"
        );
        return (int)($row['total'] ?? 0);
    }
}

?>