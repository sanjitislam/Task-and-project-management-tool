<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class ReportsModel extends Model
{
    /**
     * Build a monthly usage report for the given year-month.
     *
     * @param int $year   e.g. 2026
     * @param int $month  1-12
     * @return array      Array of rows; each row has workspace metrics for that month
     */
    public function monthlyUsage($year, $month)
    {
        // Build date range for the month
        $from = sprintf('%04d-%02d-01', $year, $month);
        $to   = date('Y-m-t', strtotime($from));  // 't' = last day of month

        return $this->db->select(
            "SELECT 
                w.id,
                w.name AS workspace,
                w.plan,
                CASE WHEN w.is_active = 1 THEN 'Active' ELSE 'Inactive' END AS status,
                
                (SELECT COUNT(*) FROM workspace_members wm 
                 WHERE wm.workspace_id = w.id) AS total_members,
                
                (SELECT COUNT(*) FROM projects p 
                 WHERE p.workspace_id = w.id 
                   AND DATE(p.created_at) BETWEEN ? AND ?) AS projects_created,
                
                (SELECT COUNT(*) FROM tasks t
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = w.id
                   AND DATE(t.created_at) BETWEEN ? AND ?) AS tasks_created,
                
                (SELECT COUNT(*) FROM tasks t
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = w.id
                   AND t.status = 'done'
                   AND DATE(t.created_at) BETWEEN ? AND ?) AS tasks_done,
                
                (SELECT COALESCE(SUM(tl.hours_logged), 0) FROM time_logs tl
                 INNER JOIN tasks t ON tl.task_id = t.id
                 INNER JOIN projects p ON t.project_id = p.id
                 WHERE p.workspace_id = w.id
                   AND DATE(tl.logged_at) BETWEEN ? AND ?) AS hours_logged,
                
                (SELECT COUNT(*) FROM activity_logs al
                 WHERE al.workspace_id = w.id
                   AND DATE(al.created_at) BETWEEN ? AND ?) AS activity_count
                 
             FROM workspaces w
             ORDER BY tasks_created DESC, w.name ASC",
            "ssssssssss",
            [$from, $to, $from, $to, $from, $to, $from, $to, $from, $to]
        );
    }
}?>