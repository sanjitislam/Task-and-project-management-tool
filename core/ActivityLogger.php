<?php
/**
 * ActivityLogger
 * --------------------------------------
 * Static helper to record events to the activity_logs table.
 * Call from any controller to log significant actions.
 *
 * Example:
 *   ActivityLogger::log('workspace_deleted', 'Deleted workspace "Acme Corp"', null, $workspaceId);
 *   ActivityLogger::log('user_login', 'Admin logged in');
 *   ActivityLogger::log('admin_created', 'New admin: ' . $name);
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class ActivityLogger
{
    /**
     * Record an activity log entry.
     *
     * @param string  $actionType   Short identifier (e.g., 'user_created')
     * @param string  $description  Human-readable text
     * @param int|null $workspaceId Optional workspace context
     * @param int|null $projectId   Optional project context
     * @return int|false  Inserted log ID, or false on failure.
     */
    public static function log($actionType, $description, $workspaceId = null, $projectId = null)
    {
        try {
            $db = Database::getInstance();

            $userId = Auth::id();   // Currently logged-in user (or null)

            return $db->execute(
                "INSERT INTO activity_logs 
                 (workspace_id, project_id, user_id, action_type, description, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                "iiiss",
                [
                    $workspaceId,
                    $projectId,
                    $userId,
                    $actionType,
                    $description
                ]
            );

        } catch (Exception $e) {
            // Don't break the main app flow if logging fails
            error_log('ActivityLogger error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get human-friendly label for an action type code.
     * Used in views to show "User Logged In" instead of "user_login".
     */
    public static function actionLabel($actionType)
    {
        $map = [
            'user_login'         => '🔑 User Login',
            'user_logout'        => '🚪 User Logout',
            'admin_created'      => '➕ Admin Created',
            'user_invited'       => '📨 User Invited',
            'user_activated'     => '✅ User Activated',
            'user_deactivated'   => '⛔ User Deactivated',
            'user_role_changed'  => '🎭 User Role Changed',
            'workspace_created'  => '🏢 Workspace Created',
            'workspace_deleted'  => '🗑️ Workspace Deleted',
            'workspace_activated'   => '✅ Workspace Activated',
            'workspace_deactivated' => '⛔ Workspace Deactivated',
            'member_removed'     => '🚫 Member Removed',
            'project_created'    => '📁 Project Created',
            'project_deleted'    => '🗑️ Project Deleted',
            'task_created'       => '✅ Task Created',
            'task_deleted'       => '🗑️ Task Deleted',
            'ticket_resolved'    => '🎫 Ticket Resolved',
            'ticket_reopened'    => '🔁 Ticket Reopened',
            'ticket_note_added'  => '📝 Ticket Note Added',
            'settings_updated'   => '⚙️ Settings Updated',
            'announcement_posted'=> '📢 Announcement Posted',
            'announcement_activated' => '✅ Announcement Activated',
            'announcement_deactivated' => '⛔ Announcement Deactivated',
            'announcement_deleted' => '🗑️ Announcement Deleted'
        ];
        return $map[$actionType] ?? '📝 ' . ucwords(str_replace('_', ' ', $actionType));
    }
}
?>