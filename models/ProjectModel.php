<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class ProjectModel extends Model
{
    /* ============ STAT METHODS (Step 4) ============ */

    public function countAll()
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM projects");
        return (int)($row['total'] ?? 0);
    }

    public function countActive()
    {
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS total FROM projects WHERE status = ?",
            "s",
            ['active']
        );
        return (int)($row['total'] ?? 0);
    }

    /* ============ NEW: MANAGEMENT METHODS ============ */

    /**
     * Get all projects with workspace name, client name, and team lead.
     * Supports filtering by workspace, status, and team lead user ID.
     */
    public function getAll($filters = [])
    {
        $sql = "
            SELECT 
                p.id, p.name, p.description, p.deadline, p.color_label,
                p.status, p.visibility, p.created_at,
                w.id AS workspace_id, w.name AS workspace_name,
                c.id AS client_id, c.name AS client_name,
                (SELECT u.name 
                 FROM workspace_members wm 
                 INNER JOIN users u ON wm.user_id = u.id 
                 WHERE wm.workspace_id = p.workspace_id 
                   AND wm.workspace_role = 'lead' 
                 LIMIT 1) AS team_lead_name,
                (SELECT u.id 
                 FROM workspace_members wm 
                 INNER JOIN users u ON wm.user_id = u.id 
                 WHERE wm.workspace_id = p.workspace_id 
                   AND wm.workspace_role = 'lead' 
                 LIMIT 1) AS team_lead_id,
                (SELECT COUNT(*) FROM tasks t 
                 WHERE t.project_id = p.id) AS task_count,
                (SELECT COUNT(*) FROM project_members pm 
                 WHERE pm.project_id = p.id) AS member_count
            FROM projects p
            LEFT JOIN workspaces w ON p.workspace_id = w.id
            LEFT JOIN users c ON p.client_id = c.id
            WHERE 1=1
        ";

        $types = '';
        $params = [];

        // Filter by workspace
        if (!empty($filters['workspace_id'])) {
            $sql .= " AND p.workspace_id = ? ";
            $types .= 'i';
            $params[] = (int)$filters['workspace_id'];
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ? ";
            $types .= 's';
            $params[] = $filters['status'];
        }

        // Filter by team lead (matches user id who has workspace_role='lead'
        // in this project's workspace)
        if (!empty($filters['team_lead_id'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM workspace_members wm 
                WHERE wm.workspace_id = p.workspace_id 
                  AND wm.workspace_role = 'lead' 
                  AND wm.user_id = ?
            ) ";
            $types .= 'i';
            $params[] = (int)$filters['team_lead_id'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        if (empty($params)) {
            return $this->db->select($sql);
        }
        return $this->db->select($sql, $types, $params);
    }

    /**
     * Get one project with full details for the view page.
     */
    public function getDetails($id)
    {
        $project = $this->db->selectOne(
            "SELECT 
                p.*,
                w.name AS workspace_name,
                c.name AS client_name,
                c.email AS client_email
             FROM projects p
             LEFT JOIN workspaces w ON p.workspace_id = w.id
             LEFT JOIN users c ON p.client_id = c.id
             WHERE p.id = ?",
            "i",
            [$id]
        );

        if (!$project) return null;

        // Get members of this project
        $project['members'] = $this->db->select(
            "SELECT 
                pm.assigned_at,
                u.id, u.name, u.email, u.role,
                wm.workspace_role
             FROM project_members pm
             INNER JOIN users u ON pm.user_id = u.id
             LEFT JOIN workspace_members wm 
                ON wm.user_id = u.id AND wm.workspace_id = ?
             WHERE pm.project_id = ?
             ORDER BY pm.assigned_at DESC",
            "ii",
            [$project['workspace_id'], $id]
        );

        // Get milestones
        $project['milestones'] = $this->db->select(
            "SELECT id, title, description, due_date, status, is_client_visible
             FROM milestones
             WHERE project_id = ?
             ORDER BY due_date ASC",
            "i",
            [$id]
        );

        // Get task stats per status
        $taskStats = $this->db->select(
            "SELECT status, COUNT(*) AS count
             FROM tasks WHERE project_id = ?
             GROUP BY status",
            "i",
            [$id]
        );

        $project['task_stats'] = [
            'todo'        => 0,
            'in_progress' => 0,
            'review'      => 0,
            'done'        => 0
        ];
        foreach ($taskStats as $ts) {
            $project['task_stats'][$ts['status']] = (int)$ts['count'];
        }
        $project['total_tasks'] = array_sum($project['task_stats']);

        return $project;
    }

    /**
     * Get a list of all team leads across all workspaces.
     * Used in the filter dropdown.
     */
    public function getAllTeamLeads()
    {
        return $this->db->select(
            "SELECT DISTINCT u.id, u.name, u.email
             FROM workspace_members wm
             INNER JOIN users u ON wm.user_id = u.id
             WHERE wm.workspace_role = 'lead'
             ORDER BY u.name ASC"
        );
    }
}
?>