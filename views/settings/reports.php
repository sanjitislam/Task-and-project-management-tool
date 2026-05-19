<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="page-header">
    <h2>⚙️ Platform Settings</h2>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>settings" class="btn btn-secondary btn-small">
            🔢 Plan Limits
        </a>
        <a href="<?= BASE_URL ?>settings/announcements" class="btn btn-secondary btn-small">
            📢 Announcements
        </a>
        <a href="<?= BASE_URL ?>settings/reports" class="btn btn-primary btn-small active-tab">
            📥 Reports
        </a>
    </div>
</div>

<div class="card form-card">
    <h3>📥 Generate Monthly Usage Report</h3>
    <p class="form-intro">
        Generate a CSV report for any month. The report includes per-workspace usage
        metrics: members, projects, tasks, completion rate, hours, and activity counts.
    </p>

    <form method="GET" action="<?= BASE_URL ?>settings/exportReport">
        <div class="form-row">
            <div class="form-group">
                <label for="year">Year</label>
                <select id="year" name="year">
                    <?php $currentYear = (int)date('Y'); for ($y = $currentYear; $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>>
                            <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="month">Month</label>
                <select id="month" name="month">
                    <?php
                    $currentMonth = (int)date('n');
                    $monthNames = [
                        1=>'January', 2=>'February', 3=>'March', 4=>'April',
                        5=>'May', 6=>'June', 7=>'July', 8=>'August',
                        9=>'September', 10=>'October', 11=>'November', 12=>'December'
                    ];
                    foreach ($monthNames as $num => $name): ?>
                        <option value="<?= $num ?>" <?= $num === $currentMonth ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                📥 Download CSV
            </button>
        </div>
    </form>
</div>

<div class="card">
    <h3>ℹ️ About the Report</h3>
    <p>The CSV report contains these columns per workspace:</p>
    <ul class="report-columns">
        <li><strong>Workspace</strong> — name</li>
        <li><strong>Plan</strong> — free or pro</li>
        <li><strong>Status</strong> — active or inactive</li>
        <li><strong>Total Members</strong> — at time of report generation</li>
        <li><strong>Projects Created</strong> — within selected month</li>
        <li><strong>Tasks Created</strong> — within selected month</li>
        <li><strong>Tasks Done</strong> — completed this month</li>
        <li><strong>Completion Rate</strong> — % of tasks done</li>
        <li><strong>Hours Logged</strong> — total billable hours</li>
        <li><strong>Activity Count</strong> — log entries in the month</li>
    </ul>
    <p class="text-muted">
        💡 You can open the CSV in Excel, Google Sheets, or any text editor.
    </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>