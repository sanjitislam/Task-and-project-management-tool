/**
 * Task Management Admin — JavaScript
 * ----------------------------------
 * Handles AJAX calls and UI interactions.
 */

document.addEventListener('DOMContentLoaded', function () {

    // -----------------------------------------------------
    // Dashboard: Refresh Stats button (AJAX)
    // -----------------------------------------------------
    const refreshBtn = document.getElementById('refreshStats');

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            // Disable button & show loading state
            refreshBtn.disabled = true;
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '⏳ Loading...';

            // The AJAX call (modern fetch API)
            fetch(BASE_URL + 'api/dashboard_stats.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update each stat on the page
                        updateStat('stat-workspaces',   data.stats.total_workspaces);
                        updateStat('stat-users',        data.stats.total_users);
                        updateStat('stat-projects',     data.stats.active_projects);
                        updateStat('stat-tasks-today',  data.stats.tasks_today);

                        // Update role counts
                        updateStat('role-admin',     data.stats.users_by_role.admin);
                        updateStat('role-team_lead', data.stats.users_by_role.team_lead);
                        updateStat('role-member',    data.stats.users_by_role.member);
                        updateStat('role-client',    data.stats.users_by_role.client);
                    } else {
                        alert('Failed to refresh: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                })
                .finally(() => {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = originalText;
                });
        });
    }

});

/**
 * Update a stat number with a brief highlight animation.
 */
function updateStat(elementId, newValue) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = newValue;
        el.classList.remove('flash-update');
        // Force browser reflow so the animation re-triggers
        void el.offsetWidth;
        el.classList.add('flash-update');
    }
}

/**
 * Global BASE_URL — set by header.php inline if needed.
 * We'll set it here as a fallback.
 */
const BASE_URL = '/task_management/';