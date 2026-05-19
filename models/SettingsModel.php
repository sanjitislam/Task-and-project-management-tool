<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class SettingsModel extends Model
{
    /* ============ PLAN LIMITS ============ */

    /**
     * Get all plan limits (free & pro).
     */
    public function getAllPlans()
    {
        return $this->db->select(
            "SELECT * FROM plan_limits ORDER BY plan ASC"
        );
    }

    /**
     * Update a plan's max_projects value.
     */
    public function updatePlanLimit($plan, $maxProjects)
    {
        if (!in_array($plan, ['free', 'pro'])) {
            return false;
        }

        return $this->db->execute(
            "UPDATE plan_limits SET max_projects = ? WHERE plan = ?",
            "is",
            [(int)$maxProjects, $plan]
        );
    }

    /* ============ ANNOUNCEMENTS ============ */

    /**
     * Get all announcements with poster info.
     */
    public function getAllAnnouncements()
    {
        return $this->db->select(
            "SELECT 
                a.id, a.title, a.message, a.is_active, a.created_at,
                u.name AS posted_by_name
             FROM announcements a
             LEFT JOIN users u ON a.posted_by = u.id
             ORDER BY a.created_at DESC"
        );
    }

    /**
     * Create a new announcement.
     */
    public function createAnnouncement($title, $message, $userId)
    {
        return $this->db->execute(
            "INSERT INTO announcements (title, message, posted_by, is_active, created_at)
             VALUES (?, ?, ?, 1, NOW())",
            "ssi",
            [$title, $message, $userId]
        );
    }

    /**
     * Find announcement by ID.
     */
    public function findAnnouncementById($id)
    {
        return $this->db->selectOne(
            "SELECT * FROM announcements WHERE id = ? LIMIT 1",
            "i",
            [$id]
        );
    }

    /**
     * Toggle active/inactive.
     */
    public function toggleAnnouncement($id)
    {
        return $this->db->execute(
            "UPDATE announcements SET is_active = NOT is_active WHERE id = ?",
            "i",
            [$id]
        );
    }

    public function getAnnouncementStatus($id)
    {
        $row = $this->db->selectOne(
            "SELECT is_active FROM announcements WHERE id = ?",
            "i",
            [$id]
        );
        return $row ? (int)$row['is_active'] : null;
    }

    /**
     * Delete an announcement.
     */
    public function deleteAnnouncement($id)
    {
        return $this->db->execute(
            "DELETE FROM announcements WHERE id = ?",
            "i",
            [$id]
        );
    }
}?>