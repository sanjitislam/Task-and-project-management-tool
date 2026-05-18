const BASE_URL = '/task_management/';

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

document.addEventListener('DOMContentLoaded', function () {

    // =============== DASHBOARD ===============
    const refreshBtn = document.getElementById('refreshStats');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            refreshBtn.disabled = true;
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '⏳ Loading...';

            fetch(BASE_URL + 'api/dashboard_stats.php')
                .then(r => r.json())
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

    // =============== WORKSPACES ===============
    const workspaceSearch = document.getElementById('workspaceSearch');
    if (workspaceSearch) {
        let timer;
        workspaceSearch.addEventListener('input', function () {
            clearTimeout(timer);
            const ind = document.getElementById('searchIndicator');
            if (ind) ind.textContent = '⏳';

            timer = setTimeout(() => {
                const q = workspaceSearch.value.trim();
                fetch(BASE_URL + 'api/workspace_search.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('workspaceList').innerHTML = data.html;
                            if (ind) ind.textContent = data.count + ' result(s)';
                            attachWorkspaceEvents();
                        }
                    })
                    .catch(err => { if (ind) ind.textContent = '❌'; });
            }, 300);
        });
    }
    attachWorkspaceEvents();

    // =============== USERS ===============
    const userSearch = document.getElementById('userSearch');
    const userRoleFilter = document.getElementById('userRoleFilter');

    function performUserSearch() {
        const ind = document.getElementById('userSearchIndicator');
        if (ind) ind.textContent = '⏳';

        const q = userSearch ? userSearch.value.trim() : '';
        const r = userRoleFilter ? userRoleFilter.value : '';

        const params = new URLSearchParams();
        if (q) params.append('q', q);
        if (r) params.append('role', r);

        fetch(BASE_URL + 'api/user_search.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('userList').innerHTML = data.html;
                    if (ind) ind.textContent = data.count + ' result(s)';
                    attachUserEvents();
                }
            })
            .catch(err => { if (ind) ind.textContent = '❌'; });
    }

    if (userSearch) {
        let userTimer;
        userSearch.addEventListener('input', function () {
            clearTimeout(userTimer);
            const ind = document.getElementById('userSearchIndicator');
            if (ind) ind.textContent = '⏳';
            userTimer = setTimeout(performUserSearch, 300);
        });
    }

    if (userRoleFilter) {
        userRoleFilter.addEventListener('change', performUserSearch);
    }

    attachUserEvents();

    // =============== PROJECTS ===============
    const projWorkspaceFilter = document.getElementById('projectWorkspaceFilter');
    const projStatusFilter    = document.getElementById('projectStatusFilter');
    const projLeadFilter      = document.getElementById('projectLeadFilter');
    const projClearBtn        = document.getElementById('clearProjectFilters');

    function performProjectFilter() {
        const ind = document.getElementById('projectFilterIndicator');
        if (ind) ind.textContent = '⏳';

        const params = new URLSearchParams();
        if (projWorkspaceFilter && projWorkspaceFilter.value) {
            params.append('workspace_id', projWorkspaceFilter.value);
        }
        if (projStatusFilter && projStatusFilter.value) {
            params.append('status', projStatusFilter.value);
        }
        if (projLeadFilter && projLeadFilter.value) {
            params.append('team_lead_id', projLeadFilter.value);
        }

        fetch(BASE_URL + 'api/project_search.php?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('projectList').innerHTML = data.html;
                    if (ind) ind.textContent = data.count + ' result(s)';
                }
            })
            .catch(err => { if (ind) ind.textContent = '❌'; });
    }

    [projWorkspaceFilter, projStatusFilter, projLeadFilter].forEach(el => {
        if (el) el.addEventListener('change', performProjectFilter);
    });

    if (projClearBtn) {
        projClearBtn.addEventListener('click', function () {
            if (projWorkspaceFilter) projWorkspaceFilter.value = '';
            if (projStatusFilter)    projStatusFilter.value = '';
            if (projLeadFilter)      projLeadFilter.value = '';
            performProjectFilter();
        });
    }

    // =============== Generic ===============
    attachDeleteConfirmations();
});

// ============ HELPERS ============

function updateStat(elementId, newValue) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = newValue;
        el.classList.remove('flash-update');
        void el.offsetWidth;
        el.classList.add('flash-update');
    }
}

function attachWorkspaceEvents() {
    document.querySelectorAll('.toggle-status').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';

            fetch(BASE_URL + 'api/workspace_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, csrf_token: getCsrfToken() })
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

function attachUserEvents() {
    document.querySelectorAll('.toggle-user-status').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';

            fetch(BASE_URL + 'api/user_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, csrf_token: getCsrfToken() })
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

    document.querySelectorAll('.role-select').forEach(sel => {
        if (sel.dataset.bound) return;
        sel.dataset.bound = '1';

        sel.addEventListener('change', function () {
            const id = this.dataset.id;
            const oldRole = this.dataset.current;
            const newRole = this.value;

            if (!confirm('Change role to "' + newRole.replace('_',' ') + '"?')) {
                this.value = oldRole;
                return;
            }

            this.disabled = true;

            fetch(BASE_URL + 'api/user_role.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: id,
                    role: newRole,
                    csrf_token: getCsrfToken()
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.className = 'role-select badge-role-' + data.new_role;
                    this.dataset.current = data.new_role;
                    flashElement(this);
                } else {
                    this.value = oldRole;
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                this.value = oldRole;
                alert('Network error: ' + err.message);
            })
            .finally(() => { this.disabled = false; });
        });
    });
}

function flashElement(el) {
    el.classList.remove('flash-update');
    void el.offsetWidth;
    el.classList.add('flash-update');
}

function attachDeleteConfirmations() {
    document.querySelectorAll('.confirm-delete').forEach(link => {
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
