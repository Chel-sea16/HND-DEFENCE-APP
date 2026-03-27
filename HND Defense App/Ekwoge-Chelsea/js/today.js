// today.js

function normalizeApiArray(data) {
    if (Array.isArray(data)) return data;
    if (data && Array.isArray(data.tasks)) return data.tasks;
    if (data && Array.isArray(data.data)) return data.data;
    return [];
}

function loadTodayTasks() {
    fetch('./php/fetch_today_tasks.php')
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            const tasks = normalizeApiArray(data);
            if (!Array.isArray(tasks)) throw new Error(data?.message || 'Invalid API response');
            displayTodayTasks(tasks);
            updateTaskCount(tasks.length);
        })
        .catch(error => {
            console.error('Error loading today\'s tasks:', error);
            const container = document.getElementById('todayTasks');
            if (container) container.innerHTML = `<p>Error loading tasks: ${error.message}</p>`;
            updateTaskCount(0);
        });
}

function displayTodayTasks(tasks) {
    const container = document.getElementById('todayTasks');
    const noTasksMessage = document.getElementById('noTasksMessage');
    if (!container) return;

    if (!Array.isArray(tasks) || tasks.length === 0) {
        container.style.display = 'none';
        if (noTasksMessage) noTasksMessage.style.display = 'block';
        return;
    }

    container.style.display = 'block';
    if (noTasksMessage) noTasksMessage.style.display = 'none';

    container.innerHTML = tasks.map(task => {
        const status = task.status || 'Pending';
        const statusClass = status.toLowerCase() === 'completed' ? 'badge-completed' : 'badge-pending';

        return `
            <div class="task-item" data-task-id="${task.id}">
                <div class="task-checkbox">
                    <input type="checkbox" id="task-${task.id}" ${status.toLowerCase() === 'completed' ? 'checked' : ''} onchange="toggleTodayTaskStatus(${task.id})">
                    <label for="task-${task.id}"></label>
                </div>
                <div class="task-content">
                    <div class="task-header">
                        <h4>${task.title}</h4>
                        <span class="badge ${statusClass}">${status}</span>
                    </div>
                    <p>${task.description || 'No description'}</p>
                    <div class="task-meta">
                        <span class="project">${task.project_name || 'No project'}</span>
                        <span class="due-date">${task.due_date || 'No due date'}</span>
                        <span class="priority ${(task.priority || '').toLowerCase()}">${task.priority || 'No priority'}</span>
                    </div>
                </div>
                <div class="task-actions">
                    <button class="btn btn-sm btn-secondary" onclick="editTodayTask(${task.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTodayTask(${task.id})"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
    }).join('');
}

function updateTaskCount(count) {
    const taskCountElem = document.getElementById('taskCount');
    if (taskCountElem) taskCountElem.textContent = count;
}

function toggleTodayTaskStatus(taskId) {
    fetch('./php/update_task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, action: 'toggle_complete' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadTodayTasks();
        } else {
            alert('Error updating task status');
        }
    })
    .catch(err => {
        console.error('Error updating task status:', err);
        alert('Error updating task status');
    });
}

function editTodayTask(taskId) {
    window.location.href = `create-task.php?edit=${taskId}`;
}

function deleteTodayTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;

    fetch('./php/update_task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, action: 'delete' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadTodayTasks();
        } else {
            alert('Error deleting task');
        }
    })
    .catch(err => {
        console.error('Error deleting task:', err);
        alert('Error deleting task');
    });
}

if (document.readyState !== 'loading') {
    loadTodayTasks();
} else {
    document.addEventListener('DOMContentLoaded', loadTodayTasks);
}
