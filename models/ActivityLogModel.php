<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class ActivityLogModel extends Model
{
    /**
     * Get all activity logs with filters.
     */
    public function getAll($filters = [])
    {
        $sql = "
            SELECT 
                al.id, al.action_type, al.description, al.created_at,
                u.id   AS user_id,    u.name  AS user_name, u.email AS user_email, u.role,
                w.id   AS workspace_id, w.name AS workspace_name,
                p.id   AS project_id, p.name AS project_name
            FROM activity_logs al
            LEFT JOIN users u      ON al.user_id      = u.id
            LEFT JOIN workspaces w ON al.workspace_id = w.id
            LEFT JOIN projects p   ON al.project_id   = p.id
            WHERE 1=1
        ";

        $types  = '';
        $params = [];

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(al.created_at) >= ? ";
            $types .= 's';
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(al.created_at) <= ? ";
            $types .= 's';
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['action_type'])) {
            $sql .= " AND al.action_type = ? ";
            $types .= 's';
            $params[] = $filters['action_type'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ? ";
            $types .= 'i';
            $params[] = (int)$filters['user_id'];
        }

        if (!empty($filters['workspace_id'])) {
            $sql .= " AND al.workspace_id = ? ";
            $types .= 'i';
            $params[] = (int)$filters['workspace_id'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT 500";

        if (empty($params)) {
            return $this->db->select($sql);
        }
        return $this->db->select($sql, $types, $params);
    }

    /**
     * Get distinct action types that exist in the database.
     * Used to populate the filter dropdown dynamically.
     */
    public function getDistinctActionTypes()
    {
        return $this->db->select(
            "SELECT DISTINCT action_type 
             FROM activity_logs 
             WHERE action_type IS NOT NULL 
             ORDER BY action_type ASC"
        );
    }

    /**
     * Get list of users who have activity log entries.
     */
    public function getDistinctUsers()
    {
        return $this->db->select(
            "SELECT DISTINCT u.id, u.name, u.email
             FROM activity_logs al
             INNER JOIN users u ON al.user_id = u.id
             ORDER BY u.name ASC"
        );
    }

    /**
     * Count total logs (for showing pagination info later).
     */
    public function countAll()
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM activity_logs");
        return (int)($row['total'] ?? 0);
    }
}
?>