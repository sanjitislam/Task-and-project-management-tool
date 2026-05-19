<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>📈 Platform Analytics</h2>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>analytics" class="btn btn-secondary btn-small">
            📊 Workspace Usage
        </a>
        <a href="<?= BASE_URL ?>analytics/platform" class="btn btn-primary btn-small active-tab">
            📈 Platform Analytics
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-label">Active Users (7d)</span>
            <span class="stat-value"><?= e($kpis['active_users_week'] ?? '0') ?></span>
        </div>
    </div>

    <div class="stat-card stat-green">
        <div class="stat-icon">📝</div>
        <div class="stat-info">
            <span class="stat-label">Tasks Created (30d)</span>
            <span class="stat-value"><?= e($kpis['tasks_30d'] ?? '0') ?></span>
        </div>
    </div>

    <div class="stat-card stat-orange">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-label">Tasks Done (30d)</span>
            <span class="stat-value"><?= e($kpis['tasks_done_30d'] ?? '0') ?></span>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-icon">📊</div>
        <div class="stat-info">
            <span class="stat-label">Completion Rate</span>
            <span class="stat-value"><?= e($kpis['completion_rate'] ?? '0') ?>%</span>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">

    <div class="card">
        <h3>🏢 Most Active Workspaces</h3>
        <p class="text-muted">Top 5 by activity log count</p>
        <div class="chart-container">
            <canvas id="topWorkspacesChart"></canvas>
        </div>
    </div>

    <div class="card">
        <h3>👤 Most Active Users</h3>
        <p class="text-muted">Top 5 by activity log count</p>
        <div class="chart-container">
            <canvas id="topUsersChart"></canvas>
        </div>
    </div>

</div>

<div class="card">
    <h3>📈 Task Activity — Last 30 Days</h3>
    <p class="text-muted">Daily task creation vs completion</p>
    <div class="chart-container chart-wide">
        <canvas id="taskTrendChart"></canvas>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script>
const topWorkspacesData = <?= json_encode([
    'labels' => array_column($topWorkspaces ?? [], 'name'),
    'values' => array_column($topWorkspaces ?? [], 'activity_count')
]) ?>;

const topUsersData = <?= json_encode([
    'labels' => array_column($topUsers ?? [], 'name'),
    'values' => array_column($topUsers ?? [], 'activity_count')
]) ?>;

const taskCreationData = <?= json_encode($taskCreationData ?? []) ?>;
const taskCompletionData = <?= json_encode($taskCompletionData ?? []) ?>;
</script>

<script>
// Wait for both DOM and Chart.js to be ready
window.addEventListener('load', function () {

    if (typeof Chart === 'undefined') {
        console.error('Chart.js failed to load.');
        return;
    }

    // ========== Top Workspaces (Horizontal Bar Chart) ==========
    const wsCtx = document.getElementById('topWorkspacesChart');
    if (wsCtx && topWorkspacesData.labels.length) {
        new Chart(wsCtx, {
            type: 'bar',
            data: {
                labels: topWorkspacesData.labels,
                datasets: [{
                    label: 'Activity Count',
                    data: topWorkspacesData.values,
                    backgroundColor: '#667eea',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // ========== Top Users (Horizontal Bar Chart) ==========
    const userCtx = document.getElementById('topUsersChart');
    if (userCtx && topUsersData.labels.length) {
        new Chart(userCtx, {
            type: 'bar',
            data: {
                labels: topUsersData.labels,
                datasets: [{
                    label: 'Activity Count',
                    data: topUsersData.values,
                    backgroundColor: '#27ae60',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // ========== Task Trend (Line Chart) ==========
    const trendCtx = document.getElementById('taskTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: taskCreationData.map(d => {
                    // Format YYYY-MM-DD as "MMM d"
                    const date = new Date(d.date);
                    return date.toLocaleDateString('en-US', {
                        month: 'short', day: 'numeric'
                    });
                }),
                datasets: [
                    {
                        label: 'Tasks Created',
                        data: taskCreationData.map(d => d.count),
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52,152,219,0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Tasks Completed',
                        data: taskCompletionData.map(d => d.count),
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39,174,96,0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>