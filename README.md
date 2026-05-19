# Task Management Admin Panel

Fixed package prepared for local XAMPP/WAMP use.

## Run locally

1. Copy the `task_management` folder into your web root, for example:
   - XAMPP: `C:\xampp\htdocs\task_management`
   - Linux/Mac: `/opt/lampp/htdocs/task_management`
2. Open phpMyAdmin and import:
   - `_database/task_management.sql`
3. Check database credentials in:
   - `config/database.php`
4. Open:
   - `http://localhost/task_management/login`

## Important fixes included

- Fixed broken Users/Workspaces search and Projects/Tasks/Activity Logs filters.
- Added the missing `SettingsController` so `/settings`, announcements, and reports work.
- Added JavaScript handlers for delete confirmation, support ticket status toggle, and announcement toggle.
- Removed public debug file and Git metadata from the final package.
- Added safer SQL import copy in `_database/task_management.sql`.
- Improved workspace delete cleanup for related projects/tasks/logs to avoid foreign-key failures.
- Added fallback helper for text excerpts when PHP mbstring is unavailable.
