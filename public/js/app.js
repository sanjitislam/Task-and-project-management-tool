/**
 * Task Management Admin — JavaScript
 */

const BASE_URL = '/task_management/';

// Helper to get the CSRF token from the page (we'll add it to a meta tag)
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

document.addEventListener('DOMContentLoaded', function () {

    // =================================================
    // DASHBOARD: Refresh Stats button
    // =================================================
    const refreshBtn = document.getElementById('refreshStats');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            refreshBtn.disabled = true;
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '⏳ Loading...';

            fetch(BASE_URL + 'api/dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStat('stat-workspaces', data.stats.total_workspaces);
                        updateStat('stat-users', data.stats.total_users);
                        updateStat('stat-projects', data.stats.active_projects);
                        updateStat('stat-tasks-today', data.stats.tasks_today);
                        updateStat('role-admin', data.stats.users_by_role.admin);
                        updateStat('role-team_lead', data.stats.users_by_role.team_lead);
                        updateStat('role-member', data.stats.users_by_role.member);
                        updateStat('role-client', data.stats.users_by_role.client);
                    }
                })
                .catch(err => alert('Error: ' + err.message))
                .finally(() => {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = originalText;
                });
        });
    }

    // ===================================================
    // WORKSPACES: Live search (AJAX)
    // =================================================
    const searchInput = document.getElementById('workspaceSearch');
    const indicator   = document.getElementById('searchIndicator');
    const listDiv     = document.getElementById('workspaceList');

    if (searchInput) {
        let debounceTimer;

        searchInput.addEventListener('input', function () {
            // Debounce — wait 300ms after typing stops
            clearTimeout(debounceTimer);
            indicator.textContent = '⏳';

            debounceTimer = setTimeout(() => {
                const query = searchInput.value.trim();

                fetch(BASE_URL + 'api/workspace_search.php?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            listDiv.innerHTML = data.html;
                            indicator.textContent = data.count + ' result(s)';
                            // Re-attach event handlers (since DOM was replaced)
                            attachWorkspaceEvents();
                        } else {
                            indicator.textContent = '❌ Error';
                        }
                    })
                    .catch(err => {
                        indicator.textContent = '❌';
                        console.error(err);
                    });
            }, 300);
        });
    }

    // =================================================
    // Initial wiring for workspace action buttons
    // =================================================
    attachWorkspaceEvents();

    // =================================================
    // Generic delete confirmation
    // =================================================
    attachDeleteConfirmations();
});

// ---------- Helpers ----------

function updateStat(elementId, newValue) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = newValue;
        el.classList.remove('flash-update');
        void el.offsetWidth;  // trigger reflow
        el.classList.add('flash-update');
    }
}

/**
 * Attach click handlers to toggle-status and delete buttons.
 * Called on initial load AND after AJAX replaces table contents.
 */
function attachWorkspaceEvents() {
    // Toggle active/inactive
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';

            fetch(BASE_URL + 'api/workspace_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: id,
                    csrf_token: getCsrfToken()
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.is_active) {
                        this.classList.remove('inactive');
                        this.classList.add('active');
                        this.innerHTML = '✅ Active';
                    } else {
                        this.classList.remove('active');
                        this.classList.add('inactive');
                        this.innerHTML = '⛔ Inactive';
                    }
                } else {
                    this.innerHTML = originalHTML;
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                this.innerHTML = originalHTML;
                alert('Network error: ' + err.message);
            })
            .finally(() => { this.disabled = false; });
        });
    });

    attachDeleteConfirmations();
}

function attachDeleteConfirmations() {
    document.querySelectorAll('.confirm-delete').forEach(link => {
        // Avoid double-binding
        if (link.dataset.bound) return;
        link.dataset.bound = '1';

        link.addEventListener('click', function (e) {
            const name = this.dataset.name || 'this item';
            if (!confirm('⚠️ Delete "' + name + '"?\n\nThis cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.confirm-remove').forEach(link => {
        if (link.dataset.bound) return;
        link.dataset.bound = '1';

        link.addEventListener('click', function (e) {
            const name = this.dataset.name || 'this member';
            if (!confirm('Remove ' + name + ' from this workspace?')) {
                e.preventDefault();
            }
        });
    });
}