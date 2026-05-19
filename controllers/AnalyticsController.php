<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class AnalyticsController extends Controller
{
    /**
     * GET /analytics
     * Show workspace usage report (the default analytics view).
     */
    public function index()
    {
        Auth::requireRole('admin');

        $model = $this->model('AnalyticsModel');
        $usage = $model->workspaceUsage();

        // Simulated storage estimate: assume 250KB average per attachment
        // (Replace with real file_size column data when available)
        $avgAttachmentKB = 250;

        foreach ($usage as &$ws) {
            $ws['storage_kb'] = $ws['attachment_count'] * $avgAttachmentKB;
            $ws['storage_mb'] = round($ws['storage_kb'] / 1024, 2);
        }
        unset($ws);

        $this->view('analytics/workspace_usage', [
            'pageTitle' => 'Workspace Usage',
            'usage'     => $usage
        ]);
    }

    /**
     * GET /analytics/platform
     * Show platform-wide analytics with charts.
     */
    public function platform()
    {
        Auth::requireRole('admin');

        $model = $this->model('AnalyticsModel');

        $kpis              = $model->platformKpis();
        $topWorkspaces     = $model->mostActiveWorkspaces(5);
        $topUsers          = $model->mostActiveUsers(5);
        $taskCreationData  = $model->taskCreationByDay(30);
        $taskCompletionData = $model->taskCompletionByDay(30);

        $this->view('analytics/platform', [
            'pageTitle'         => 'Platform Analytics',
            'kpis'              => $kpis,
            'topWorkspaces'     => $topWorkspaces,
            'topUsers'          => $topUsers,
            'taskCreationData'  => $taskCreationData,
            'taskCompletionData' => $taskCompletionData
        ]);
    }
}
?>