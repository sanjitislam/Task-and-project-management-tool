/**
 * Task Management Admin — JavaScript
 *
 * Handles AJAX search/filter/toggle interactions across the admin panel.
 */

const BASE_URL = (function () {
    const meta = document.querySelector('meta[name="app-base-url"]');
    const base = meta ? meta.getAttribute('content') : '/task_management/';
    return base.endsWith('/') ? base : base + '/';
})();

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function qs(id) {
    return document.getElementById(id);
}

function setIndicator(id, text) {
    const el = qs(id);
    if (el) el.textContent = text || '';
}

function encodeParams(obj) {
    const params = new URLSearchParams();
    Object.keys(obj).forEach(key => {
        if (obj[key] !== undefined && obj[key] !== null && obj[key] !== '') {
            params.append(key, obj[key]);
        }
    });
    return params.toString();
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const text = await response.text();

    let data;
    try {
        data = text ? JSON.parse(text) : {};
    } catch (e) {
        throw new Error('Invalid server response. Please check PHP errors/logs.');
    }

    if (!response.ok || data.success === false) {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
}

function updateStat(elementId, newValue) {
    const el = qs(elementId);
    if (el) {
        el.textContent = newValue;
        flashElement(el);
    }
}

function flashElement(el) {
    if (!el) return;
    el.classList.remove('flash-update');
    void el.offsetWidth;
    el.classList.add('flash-update');
}

function bindOnce(el, eventName, handler) {
    if (!el) return;
    const key = 'bound' + eventName;
    if (el.dataset[key]) return;
    el.dataset[key] = '1';
    el.addEventListener(eventName, handler);
}

function updateTableHtml(containerId, html) {
    const container = qs(containerId);
    if (container) container.innerHTML = html;
}

function showAjaxError(indicatorId, error) {
    setIndicator(indicatorId, '❌ ' + (error && error.message ? error.message : 'Error'));
}

document.addEventListener('DOMContentLoaded', function () {
    // =============== DASHBOARD ===============
    const refreshBtn = qs('refreshStats');
    bindOnce(refreshBtn, 'click', function () {
        const originalText = refreshBtn.innerHTML;
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '⏳ Loading...';

        fetchJson(BASE_URL + 'api/dashboard_stats.php')
            .then(data => {
                updateStat('stat-workspaces', data.stats.total_workspaces);
                updateStat('stat-users', data.stats.total_users);
                updateStat('stat-projects', data.stats.active_projects);
                updateStat('stat-tasks-today', data.stats.tasks_today);
                updateStat('role-admin', data.stats.users_by_role.admin);
                updateStat('role-team_lead', data.stats.users_by_role.team_lead);
                updateStat('role-member', data.stats.users_by_role.member);
                updateStat('role-client', data.stats.users_by_role.client);
            })
            .catch(err => alert('Error: ' + err.message))
            .finally(() => {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = originalText;
            });
    });

    // =============== WORKSPACES ===============
    const workspaceSearch = qs('workspaceSearch');
    if (workspaceSearch) {
        let timer;
        workspaceSearch.addEventListener('input', function () {
            clearTimeout(timer);
            setIndicator('searchIndicator', '⏳');

            timer = setTimeout(() => {
                const q = workspaceSearch.value.trim();
                fetchJson(BASE_URL + 'api/workspace_search.php?' + encodeParams({ q }))
                    .then(data => {
                        updateTableHtml('workspaceList', data.html);
                        setIndicator('searchIndicator', data.count + ' result(s)');
                        attachWorkspaceEvents();
                    })
                    .catch(err => showAjaxError('searchIndicator', err));
            }, 300);
        });
    }
    attachWorkspaceEvents();

    // =============== USERS ===============
    const userSearch = qs('userSearch');
    const userRoleFilter = qs('userRoleFilter');

    function performUserSearch() {
        setIndicator('userSearchIndicator', '⏳');
        const q = userSearch ? userSearch.value.trim() : '';
        const role = userRoleFilter ? userRoleFilter.value : '';

        fetchJson(BASE_URL + 'api/user_search.php?' + encodeParams({ q, role }))
            .then(data => {
                updateTableHtml('userList', data.html);
                setIndicator('userSearchIndicator', data.count + ' result(s)');
                attachUserEvents();
            })
            .catch(err => showAjaxError('userSearchIndicator', err));
    }

    if (userSearch) {
        let userTimer;
        userSearch.addEventListener('input', function () {
            clearTimeout(userTimer);
            setIndicator('userSearchIndicator', '⏳');
            userTimer = setTimeout(performUserSearch, 300);
        });
    }

    if (userRoleFilter) {
        userRoleFilter.addEventListener('change', performUserSearch);
    }
    attachUserEvents();

    // =============== PROJECTS ===============
    const projWorkspaceFilter = qs('projectWorkspaceFilter');
    const projStatusFilter    = qs('projectStatusFilter');
    const projLeadFilter      = qs('projectLeadFilter');
    const projClearBtn        = qs('clearProjectFilters');

    function performProjectFilter() {
        setIndicator('projectFilterIndicator', '⏳');
        fetchJson(BASE_URL + 'api/project_search.php?' + encodeParams({
            workspace_id: projWorkspaceFilter ? projWorkspaceFilter.value : '',
            status: projStatusFilter ? projStatusFilter.value : '',
            team_lead_id: projLeadFilter ? projLeadFilter.value : ''
        }))
            .then(data => {
                updateTableHtml('projectList', data.html);
                setIndicator('projectFilterIndicator', data.count + ' result(s)');
            })
            .catch(err => showAjaxError('projectFilterIndicator', err));
    }

    [projWorkspaceFilter, projStatusFilter, projLeadFilter].forEach(el => {
        if (el) el.addEventListener('change', performProjectFilter);
    });

    bindOnce(projClearBtn, 'click', function (e) {
        e.preventDefault();
        if (projWorkspaceFilter) projWorkspaceFilter.value = '';
        if (projStatusFilter)    projStatusFilter.value = '';
        if (projLeadFilter)      projLeadFilter.value = '';
        performProjectFilter();
    });

    // =============== TASKS ===============
    const taskStatusFilter   = qs('taskStatusFilter');
    const taskPriorityFilter = qs('taskPriorityFilter');
    const taskAssigneeFilter = qs('taskAssigneeFilter');
    const taskProjectFilter  = qs('taskProjectFilter');
    const taskClearBtn       = qs('clearTaskFilters');

    function performTaskFilter() {
        setIndicator('taskFilterIndicator', '⏳');
        fetchJson(BASE_URL + 'api/task_search.php?' + encodeParams({
            status: taskStatusFilter ? taskStatusFilter.value : '',
            priority: taskPriorityFilter ? taskPriorityFilter.value : '',
            assignee_id: taskAssigneeFilter ? taskAssigneeFilter.value : '',
            project_id: taskProjectFilter ? taskProjectFilter.value : ''
        }))
            .then(data => {
                updateTableHtml('taskList', data.html);
                setIndicator('taskFilterIndicator', data.count + ' result(s)');
            })
            .catch(err => showAjaxError('taskFilterIndicator', err));
    }

    [taskStatusFilter, taskPriorityFilter, taskAssigneeFilter, taskProjectFilter].forEach(el => {
        if (el) el.addEventListener('change', performTaskFilter);
    });

    bindOnce(taskClearBtn, 'click', function (e) {
        e.preventDefault();
        if (taskStatusFilter)   taskStatusFilter.value = '';
        if (taskPriorityFilter) taskPriorityFilter.value = '';
        if (taskAssigneeFilter) taskAssigneeFilter.value = '';
        if (taskProjectFilter)  taskProjectFilter.value = '';
        performTaskFilter();
    });

    // =============== ACTIVITY LOGS ===============
    const logFromDate        = qs('logFromDate');
    const logToDate          = qs('logToDate');
    const logActionFilter    = qs('logActionFilter');
    const logUserFilter      = qs('logUserFilter');
    const logWorkspaceFilter = qs('logWorkspaceFilter');
    const logClearBtn        = qs('clearLogFilters');

    function performLogFilter() {
        setIndicator('logFilterIndicator', '⏳');
        fetchJson(BASE_URL + 'api/activity_log_search.php?' + encodeParams({
            from_date: logFromDate ? logFromDate.value : '',
            to_date: logToDate ? logToDate.value : '',
            action_type: logActionFilter ? logActionFilter.value : '',
            user_id: logUserFilter ? logUserFilter.value : '',
            workspace_id: logWorkspaceFilter ? logWorkspaceFilter.value : ''
        }))
            .then(data => {
                updateTableHtml('logList', data.html);
                setIndicator('logFilterIndicator', data.count + ' entries');
            })
            .catch(err => showAjaxError('logFilterIndicator', err));
    }

    [logFromDate, logToDate, logActionFilter, logUserFilter, logWorkspaceFilter].forEach(el => {
        if (el) el.addEventListener('change', performLogFilter);
    });

    bindOnce(logClearBtn, 'click', function (e) {
        e.preventDefault();
        if (logFromDate)        logFromDate.value = '';
        if (logToDate)          logToDate.value = '';
        if (logActionFilter)    logActionFilter.value = '';
        if (logUserFilter)      logUserFilter.value = '';
        if (logWorkspaceFilter) logWorkspaceFilter.value = '';
        performLogFilter();
    });

    // =============== SUPPORT TICKETS / ANNOUNCEMENTS / GENERIC ===============
    attachTicketEvents();
    attachAnnouncementEvents();
    attachDeleteConfirmations();
});

// ============ WORKSPACE EVENTS ============
function attachWorkspaceEvents() {
    document.querySelectorAll('.toggle-status').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';

            fetchJson(BASE_URL + 'api/workspace_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, csrf_token: getCsrfToken() })
            })
                .then(data => {
                    this.classList.toggle('active', !!data.is_active);
                    this.classList.toggle('inactive', !data.is_active);
                    this.innerHTML = data.is_active ? '✅ Active' : '⛔ Inactive';
                    flashElement(this);
                })
                .catch(err => {
                    this.innerHTML = originalHTML;
                    alert('Error: ' + err.message);
                })
                .finally(() => { this.disabled = false; });
        });
    });

    attachDeleteConfirmations();
}

// ============ USER EVENTS ============
function attachUserEvents() {
    document.querySelectorAll('.toggle-user-status').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';

            fetchJson(BASE_URL + 'api/user_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, csrf_token: getCsrfToken() })
            })
                .then(data => {
                    this.classList.toggle('active', !!data.is_active);
                    this.classList.toggle('inactive', !data.is_active);
                    this.innerHTML = data.is_active ? '✅ Active' : '⛔ Inactive';
                    flashElement(this);
                })
                .catch(err => {
                    this.innerHTML = originalHTML;
                    alert('Error: ' + err.message);
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

            if (!confirm('Change role to "' + newRole.replace('_', ' ') + '"?')) {
                this.value = oldRole;
                return;
            }

            this.disabled = true;
            fetchJson(BASE_URL + 'api/user_role.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, role: newRole, csrf_token: getCsrfToken() })
            })
                .then(data => {
                    this.className = 'role-select badge-role-' + data.new_role;
                    this.dataset.current = data.new_role;
                    flashElement(this);
                })
                .catch(err => {
                    this.value = oldRole;
                    alert('Error: ' + err.message);
                })
                .finally(() => { this.disabled = false; });
        });
    });
}

// ============ SUPPORT TICKET EVENTS ============
function attachTicketEvents() {
    document.querySelectorAll('.toggle-ticket').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';

            fetchJson(BASE_URL + 'api/ticket_resolve.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, csrf_token: getCsrfToken() })
            })
                .then(data => {
                    this.classList.remove('open', 'resolved');
                    this.classList.add(data.new_status);
                    this.innerHTML = data.new_status === 'open' ? '🔴 Open' : '✅ Resolved';
                    flashElement(this);
                })
                .catch(err => {
                    this.innerHTML = originalHTML;
                    alert('Error: ' + err.message);
                })
                .finally(() => { this.disabled = false; });
        });
    });
}

// ============ ANNOUNCEMENT EVENTS ============
function attachAnnouncementEvents() {
    document.querySelectorAll('.toggle-announcement').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '⏳';

            fetchJson(BASE_URL + 'api/announcement_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, csrf_token: getCsrfToken() })
            })
                .then(data => {
                    this.classList.toggle('active', !!data.is_active);
                    this.classList.toggle('inactive', !data.is_active);
                    this.innerHTML = data.is_active ? '✅ Active' : '⛔ Inactive';

                    const item = this.closest('.announcement-item');
                    if (item) item.classList.toggle('inactive', !data.is_active);
                    flashElement(this);
                })
                .catch(err => {
                    this.innerHTML = originalHTML;
                    alert('Error: ' + err.message);
                })
                .finally(() => { this.disabled = false; });
        });
    });
}

// ============ GENERIC CONFIRMATIONS ============
function attachDeleteConfirmations() {
    document.querySelectorAll('.confirm-delete, .confirm-remove').forEach(link => {
        if (link.dataset.boundConfirm) return;
        link.dataset.boundConfirm = '1';

        link.addEventListener('click', function (e) {
            const name = this.dataset.name || 'this item';
            const isRemove = this.classList.contains('confirm-remove');
            const verb = isRemove ? 'remove' : 'delete';
            const message = 'Are you sure you want to ' + verb + ' "' + name + '"? This action cannot be undone.';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}
