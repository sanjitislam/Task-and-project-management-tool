<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class TaskModel extends Model
{
    /* ============ STAT METHODS (Step 4) ============ */

    public function countAll()
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM tasks");
        return (int)($row['total'] ?? 0);
    }

    public function countCreatedToday()
    {
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS total 
             FROM tasks 
             WHERE DATE(created_at) = CURDATE()"
        );
        return (int)($row['total'] ?? 0);
    }

    /* ============ NEW: MANAGEMENT METHODS ============ */

    /**
     * Get all tasks with project, assignee, creator details.
     * Supports filtering.
     */
    public function getAll($filters = [])
    {
        $sql = "
            SELECT 
                t.id, t.title, t.description, t.priority, t.status,
                t.due_date, t.estimated_hours, t.created_at,
                p.id   AS project_id, p.name AS project_name,
                w.name AS workspace_name,
                a.id AS assignee_id, a.name AS assignee_name, a.email AS assignee_email,
                c.name AS creator_name,
                m.title AS milestone_title
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN workspaces w ON p.workspace_id = w.id
            LEFT JOIN users a ON t.assigned_to = a.id
            LEFT JOIN users c ON t.created_by = c.id
            LEFT JOIN milestones m ON t.milestone_id = m.id
            WHERE 1=1
        ";

        $types  = '';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ? ";
            $types .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = ? ";
            $types .= 's';
            $params[] = $filters['priority'];
        }

        if (!empty($filters['assignee_id'])) {
            $sql .= " AND t.assigned_to = ? ";
            $types .= 'i';
            $params[] = (int)$filters['assignee_id'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND t.project_id = ? ";
            $types .= 'i';
            $params[] = (int)$filters['project_id'];
        }

        $sql .= " ORDER BY t.created_at DESC";

        if (empty($params)) {
            return $this->db->select($sql);
        }
        return $this->db->select($sql, $types, $params);
    }

    /**
     * Get one task with full details.
     */
    public function getDetails($id)
    {
        $task = $this->db->selectOne(
            "SELECT 
                t.*,
                p.id   AS project_id, p.name AS project_name,
                w.id   AS workspace_id, w.name AS workspace_name,
                a.id AS assignee_id, a.name AS assignee_name, a.email AS assignee_email,
                c.id AS creator_id, c.name AS creator_name,
                m.title AS milestone_title
             FROM tasks t
             LEFT JOIN projects p ON t.project_id = p.id
             LEFT JOIN workspaces w ON p.workspace_id = w.id
             LEFT JOIN users a ON t.assigned_to = a.id
             LEFT JOIN users c ON t.created_by = c.id
             LEFT JOIN milestones m ON t.milestone_id = m.id
             WHERE t.id = ?",
            "i",
            [$id]
        );

        if (!$task) return null;

        // Comments
        $task['comments'] = $this->db->select(
            "SELECT 
                cm.id, cm.body, cm.is_internal, cm.created_at,
                u.name AS user_name, u.role AS user_role
             FROM comments cm
             LEFT JOIN users u ON cm.user_id = u.id
             WHERE cm.task_id = ?
             ORDER BY cm.created_at DESC",
            "i",
            [$id]
        );

        // Time logs
        $task['time_logs'] = $this->db->select(
            "SELECT 
                tl.hours_logged, tl.note, tl.logged_at,
                u.name AS user_name
             FROM time_logs tl
             LEFT JOIN users u ON tl.user_id = u.id
             WHERE tl.task_id = ?
             ORDER BY tl.logged_at DESC",
            "i",
            [$id]
        );

        // Attachments
        $task['attachments'] = $this->db->select(
            "SELECT 
                ta.file_path, ta.file_name, ta.uploaded_at,
                u.name AS uploader_name
             FROM task_attachments ta
             LEFT JOIN users u ON ta.uploaded_by = u.id
             WHERE ta.task_id = ?
             ORDER BY ta.uploaded_at DESC",
            "i",
            [$id]
        );

        // Total hours logged
        $totalHours = $this->db->selectOne(
            "SELECT COALESCE(SUM(hours_logged), 0) AS total 
             FROM time_logs WHERE task_id = ?",
            "i",
            [$id]
        );
        $task['total_hours_logged'] = (float)($totalHours['total'] ?? 0);

        return $task;
    }

    /**
     * Get list of users that have tasks assigned to them.
     * Used to populate the assignee filter dropdown.
     */
    public function getAllAssignees()
    {
        return $this->db->select(
            "SELECT DISTINCT u.id, u.name, u.email
             FROM tasks t
             INNER JOIN users u ON t.assigned_to = u.id
             ORDER BY u.name ASC"
        );
    }

    /**
     * Get a list of all projects (simple) — used in filter.
     */
    public function getAllProjectsSimple()
    {
        return $this->db->select(
            "SELECT id, name FROM projects ORDER BY name ASC"
        );
    }
}

?>