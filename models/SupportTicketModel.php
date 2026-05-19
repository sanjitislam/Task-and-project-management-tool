<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class SupportTicketModel extends Model
{
    /**
     * Get all tickets with submitter info.
     * Optional: filter by status.
     */
    public function getAll($status = '')
    {
        $sql = "
            SELECT 
                t.id, t.subject, t.message, t.status, t.admin_notes, t.created_at,
                u.id   AS user_id, u.name AS user_name, u.email AS user_email,
                u.role AS user_role
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";

        $types = '';
        $params = [];

        if (!empty($status)) {
            $sql .= " AND t.status = ? ";
            $types .= 's';
            $params[] = $status;
        }

        $sql .= " ORDER BY 
                    CASE t.status WHEN 'open' THEN 1 ELSE 2 END,
                    t.created_at DESC";

        if (empty($params)) {
            return $this->db->select($sql);
        }
        return $this->db->select($sql, $types, $params);
    }

    /**
     * Get a single ticket with full details.
     */
    public function findById($id)
    {
        return $this->db->selectOne(
            "SELECT 
                t.*,
                u.id AS user_id, u.name AS user_name, u.email AS user_email,
                u.role AS user_role, u.phone AS user_phone
             FROM support_tickets t
             LEFT JOIN users u ON t.user_id = u.id
             WHERE t.id = ?",
            "i",
            [$id]
        );
    }

    /**
     * Update admin notes on a ticket.
     */
    public function updateNotes($id, $notes)
    {
        return $this->db->execute(
            "UPDATE support_tickets SET admin_notes = ? WHERE id = ?",
            "si",
            [$notes, $id]
        );
    }

    /**
     * Set status (open or resolved).
     */
    public function setStatus($id, $status)
    {
        if (!in_array($status, ['open', 'resolved'])) {
            return false;
        }

        return $this->db->execute(
            "UPDATE support_tickets SET status = ? WHERE id = ?",
            "si",
            [$status, $id]
        );
    }

    /**
     * Count tickets per status (for the dashboard / quick stats).
     */
    public function countByStatus()
    {
        $rows = $this->db->select(
            "SELECT status, COUNT(*) AS total 
             FROM support_tickets 
             GROUP BY status"
        );

        $counts = ['open' => 0, 'resolved' => 0];
        foreach ($rows as $r) {
            $counts[$r['status']] = (int)$r['total'];
        }
        return $counts;
    }
} ?>