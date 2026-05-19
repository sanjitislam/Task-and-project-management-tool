<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class SettingsController extends Controller
{
    /**
     * GET/POST /settings
     * Manage free/pro plan project limits.
     */
    public function index()
    {
        Auth::requireRole('admin');

        $errors = [];
        $model = $this->model('SettingsModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf($_POST['csrf_token'] ?? '')) {
                set_flash('error', 'Invalid form submission. Please try again.');
                redirect('settings');
            }

            $freeMax = filter_input(INPUT_POST, 'free_max', FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 1000]
            ]);
            $proMax = filter_input(INPUT_POST, 'pro_max', FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 10000]
            ]);

            if ($freeMax === false || $freeMax === null) {
                $errors['free_max'] = 'Free plan limit must be between 1 and 1000.';
            }
            if ($proMax === false || $proMax === null) {
                $errors['pro_max'] = 'Pro plan limit must be between 1 and 10000.';
            }
            if (empty($errors) && $proMax < $freeMax) {
                $errors['pro_max'] = 'Pro plan limit must be greater than or equal to the free plan limit.';
            }

            if (empty($errors)) {
                $okFree = $model->updatePlanLimit('free', $freeMax);
                $okPro  = $model->updatePlanLimit('pro', $proMax);

                if ($okFree !== false && $okPro !== false) {
                    ActivityLogger::log(
                        'settings_updated',
                        'Plan limits updated: free=' . $freeMax . ', pro=' . $proMax
                    );
                    set_flash('success', 'Plan limits updated successfully.');
                    redirect('settings');
                }

                $errors['general'] = 'Failed to update plan limits. Please try again.';
            }
        }

        $plansRaw = $model->getAllPlans();
        $plans = [];
        foreach ($plansRaw as $plan) {
            $plans[$plan['plan']] = $plan;
        }

        $this->view('settings/plan_limits', [
            'pageTitle' => 'Settings',
            'plans'     => $plans,
            'errors'    => $errors
        ]);
    }

    /**
     * GET/POST /settings/announcements
     * Create and list announcements.
     */
    public function announcements()
    {
        Auth::requireRole('admin');

        $errors = [];
        $model = $this->model('SettingsModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf($_POST['csrf_token'] ?? '')) {
                set_flash('error', 'Invalid form submission. Please try again.');
                redirect('settings/announcements');
            }

            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($title === '') {
                $errors['title'] = 'Title is required.';
            } elseif (mb_safe_strlen($title) > 200) {
                $errors['title'] = 'Title must not exceed 200 characters.';
            }

            if ($message === '') {
                $errors['message'] = 'Message is required.';
            } elseif (mb_safe_strlen($message) > 2000) {
                $errors['message'] = 'Message must not exceed 2000 characters.';
            }

            if (empty($errors)) {
                $newId = $model->createAnnouncement($title, $message, Auth::id());

                if ($newId) {
                    ActivityLogger::log('announcement_posted', 'Announcement posted: ' . $title);
                    set_flash('success', 'Announcement posted successfully.');
                    clear_old();
                    redirect('settings/announcements');
                }

                $errors['general'] = 'Failed to post announcement. Please try again.';
            }

            flash_old($_POST);
        }

        $announcements = $model->getAllAnnouncements();

        $this->view('settings/announcements', [
            'pageTitle'     => 'Announcements',
            'announcements' => $announcements,
            'errors'        => $errors
        ]);

        clear_old();
    }

    /**
     * GET /settings/deleteAnnouncement/{id}
     */
    public function deleteAnnouncement($id = null)
    {
        Auth::requireRole('admin');

        $id = (int)$id;
        if ($id <= 0) {
            set_flash('error', 'Invalid announcement ID.');
            redirect('settings/announcements');
        }

        if (!verify_csrf($_GET['csrf_token'] ?? '')) {
            set_flash('error', 'Security token invalid. Please try again.');
            redirect('settings/announcements');
        }

        $model = $this->model('SettingsModel');
        $announcement = $model->findAnnouncementById($id);

        if (!$announcement) {
            set_flash('error', 'Announcement not found.');
            redirect('settings/announcements');
        }

        if ($model->deleteAnnouncement($id)) {
            ActivityLogger::log('announcement_deleted', 'Announcement deleted: ' . $announcement['title']);
            set_flash('success', 'Announcement deleted successfully.');
        } else {
            set_flash('error', 'Failed to delete announcement.');
        }

        redirect('settings/announcements');
    }

    /**
     * GET /settings/reports
     */
    public function reports()
    {
        Auth::requireRole('admin');

        $this->view('settings/reports', [
            'pageTitle' => 'Reports'
        ]);
    }

    /**
     * GET /settings/exportReport?year=YYYY&month=M
     * Download monthly usage CSV.
     */
    public function exportReport()
    {
        Auth::requireRole('admin');

        $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 2020, 'max_range' => 2100]
        ]);
        $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 12]
        ]);

        if (!$year) $year = (int)date('Y');
        if (!$month) $month = (int)date('n');

        $reportModel = $this->model('ReportsModel');
        $rows = $reportModel->monthlyUsage($year, $month);

        $filename = sprintf('monthly_usage_%04d_%02d.csv', $year, $month);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Workspace', 'Plan', 'Status', 'Total Members', 'Projects Created',
            'Tasks Created', 'Tasks Done', 'Completion Rate (%)', 'Hours Logged', 'Activity Count'
        ]);

        foreach ($rows as $row) {
            $tasksCreated = (int)($row['tasks_created'] ?? 0);
            $tasksDone = (int)($row['tasks_done'] ?? 0);
            $completionRate = $tasksCreated > 0 ? round(($tasksDone / $tasksCreated) * 100, 2) : 0;

            fputcsv($out, [
                $row['workspace'] ?? '',
                $row['plan'] ?? '',
                $row['status'] ?? '',
                $row['total_members'] ?? 0,
                $row['projects_created'] ?? 0,
                $tasksCreated,
                $tasksDone,
                $completionRate,
                $row['hours_logged'] ?? 0,
                $row['activity_count'] ?? 0,
            ]);
        }

        fclose($out);
        exit;
    }
}
?>
