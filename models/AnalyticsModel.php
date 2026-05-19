<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class AnalyticsModel extends Model
{
    /**
     * Per-workspace usage report.
     * Returns each workspace with task counts, member counts, project counts,
     * and a simulated storage estimate based on attachment count.
     */
    public function workspaceUsage()
    {
        return $this->db->select(
            "SELECT 
                w.id, w.name, w.plan, w.is_active, w.created_at,
                
                -- Members
                (SELECT COUNT(*) FROM workspace_members wm 
                 WHERE wm.workspace_id = w.id) AS member_count,
                
                -- Projects (all)
                (SELECT COUNT(*) FROM projects p 
                 WHERE p.workspace_id = w.id) AS project_count,
                
                -- Active projects
                (SELECT COUNT(*) FROM projects p 
                 WHERE p.workspace_id = w.id AND p.status = 'active') AS active_projects,
                
                -- All tasks
                (SELECT COUNT(*) FROM tasks t
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = w.id) AS task_count,
                
                -- Completed tasks
                (SELECT COUNT(*) FROM tasks t
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = w.id AND t.status = 'done') AS done_count,
                
                -- Attachments (used for simulated storage)
                (SELECT COUNT(*) FROM task_attachments ta
                 INNER JOIN tasks t ON ta.task_id = t.id
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = w.id) AS attachment_count,
                
                -- Total logged hours
                (SELECT COALESCE(SUM(tl.hours_logged), 0) FROM time_logs tl
                 INNER JOIN tasks t ON tl.task_id = t.id
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = w.id) AS hours_logged
                 
             FROM workspaces w
             ORDER BY task_count DESC, w.created_at DESC"
        );
    }

    /**
     * Top N most active workspaces by activity log count.
     */
    public function mostActiveWorkspaces($limit = 5)
    {
        return $this->db->select(
            "SELECT 
                w.id, w.name,
                COUNT(al.id) AS activity_count
             FROM workspaces w
             LEFT JOIN activity_logs al ON al.workspace_id = w.id
             GROUP BY w.id, w.name
             ORDER BY activity_count DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Top N most active users by activity log count.
     */
    public function mostActiveUsers($limit = 5)
    {
        return $this->db->select(
            "SELECT 
                u.id, u.name, u.role,
                COUNT(al.id) AS activity_count
             FROM users u
             LEFT JOIN activity_logs al ON al.user_id = u.id
             WHERE u.is_active = 1
             GROUP BY u.id, u.name, u.role
             ORDER BY activity_count DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Task creation count per day for last N days.
     * Returns array of ['date' => 'YYYY-MM-DD', 'count' => N].
     */
    public function taskCreationByDay($days = 30)
    {
        $rows = $this->db->select(
            "SELECT 
                DATE(created_at) AS date,
                COUNT(*) AS count
             FROM tasks
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );

        return $this->fillMissingDates($rows, $days);
    }

    /**
     * Task completion count per day for last N days.
     * Uses tasks marked as 'done', grouped by their created_at as a proxy
     * (since we don't have a 'completed_at' column).
     *
     * For accuracy in a real app, add a 'completed_at' column.
     */
    public function taskCompletionByDay($days = 30)
    {
        $rows = $this->db->select(
            "SELECT 
                DATE(created_at) AS date,
                COUNT(*) AS count
             FROM tasks
             WHERE status = 'done'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );

        return $this->fillMissingDates($rows, $days);
    }

    /**
     * Helper: fill in dates with 0 count for days with no records.
     * Ensures the chart has continuous data points.
     */
    private function fillMissingDates($rows, $days)
    {
        // Build lookup map
        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['date']] = (int)$r['count'];
        }

        // Generate all dates in the range
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $result[] = [
                'date'  => $date,
                'count' => $byDate[$date] ?? 0
            ];
        }
        return $result;
    }

    /**
     * Overall platform KPIs.
     */
    public function platformKpis()
    {
        $kpis = [];

        // Active users this week
        $row = $this->db->selectOne(
            "SELECT COUNT(DISTINCT user_id) AS total
             FROM activity_logs
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $kpis['active_users_week'] = (int)($row['total'] ?? 0);

        // Total tasks created last 30 days
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS total FROM tasks
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $kpis['tasks_30d'] = (int)($row['total'] ?? 0);

        // Total tasks completed last 30 days
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS total FROM tasks
             WHERE status='done'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $kpis['tasks_done_30d'] = (int)($row['total'] ?? 0);

        // Completion rate
        $kpis['completion_rate'] = $kpis['tasks_30d'] > 0
            ? round(($kpis['tasks_done_30d'] / $kpis['tasks_30d']) * 100, 1)
            : 0;

        return $kpis;
    }
} ?>