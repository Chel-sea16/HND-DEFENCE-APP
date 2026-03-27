// my-tasks.js - Load tasks from API

function loadTasks() {
    fetch("./api/get_tasks.php")
        .then(res => res.json())
        .then(data => {
            const tasks = Array.isArray(data) ? data : (data.tasks || []);
            
            const container = document.getElementById("taskList");
            
            if (!container) return;
            
            if (!tasks || tasks.length === 0) {
                container.innerHTML = '<p>No tasks found. <a href="create-task.php">Create your first task</a></p>';
                return;
            }
            
            container.innerHTML = "";
            
            tasks.forEach(task => {
                const status = task.status || 'Pending';
                const statusClass = status.toLowerCase() === 'completed' ? 'badge-completed' : 'badge-pending';
                const dueDate = task.due_date || 'No due date';
                const projectName = task.project_name || 'No project';
                const priority = task.priority || 'No priority';
                
                const taskCard = document.createElement('div');
                taskCard.className = 'task-item';
                taskCard.setAttribute('data-task-id', task.id);
                taskCard.innerHTML = `
                    <div class="task-checkbox">
                        <input type="checkbox" id="task-${task.id}" ${status.toLowerCase() === 'completed' ? 'checked' : ''} onchange="toggleTaskStatus(${task.id})">
                        <label for="task-${task.id}"></label>
                    </div>
                    <div class="task-content">
                        <div class="task-header">
                            <h4>${task.title}</h4>
                            <span class="badge ${statusClass}">${status}</span>
                        </div>
                        <p>${task.description || 'No description'}</p>
                        <div class="task-meta">
                            <span class="project">${projectName}</span>
                            <span class="due-date">${dueDate}</span>
                            <span class="priority ${priority.toLowerCase()}">${priority}</span>
                        </div>
                    </div>
                    <div class="task-actions">
                        <button class="btn btn-sm btn-secondary" onclick="editTask(${task.id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.id})"><i class="bi bi-trash"></i></button>
                    </div>
                `;
                container.appendChild(taskCard);
            });
        })
        .catch(err => {
            console.error("Error loading tasks:", err);
            const container = document.getElementById("taskList");
            if (container) {
                container.innerHTML = `<p>Error loading tasks: ${err.message}</p>`;
            }
        });
}

function loadProjectsForFilter() {
    fetch("./api/get_projects.php")
        .then(res => res.json())
        .then(data => {
            const projects = Array.isArray(data) ? data : (data.projects || []);
            const projectFilter = document.getElementById('projectFilter');
            if (!projectFilter) return;
            
            projectFilter.innerHTML = '<option value="">All Projects</option>';
            projects.forEach(project => {
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.project_name || project.name || 'Unnamed';
                projectFilter.appendChild(option);
            });
        })
        .catch(err => console.error('Error loading projects:', err));
}

function toggleTaskStatus(taskId) {
    fetch('./php/update_task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, action: 'toggle_complete' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadTasks();
        } else {
            alert('Error updating task status');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error updating task status');
    });
}

function editTask(taskId) {
    window.location.href = `create-task.php?edit=${taskId}`;
}

function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    fetch('./php/update_task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, action: 'delete' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadTasks();
        } else {
            alert('Error deleting task');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error deleting task');
    });
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        loadProjectsForFilter();
        loadTasks();
        
        // Setup filter listeners
        const statusFilter = document.getElementById('statusFilter');
        const priorityFilter = document.getElementById('priorityFilter');
        const projectFilter = document.getElementById('projectFilter');
        
        if (statusFilter) statusFilter.addEventListener('change', loadTasks);
        if (priorityFilter) priorityFilter.addEventListener('change', loadTasks);
        if (projectFilter) projectFilter.addEventListener('change', loadTasks);
    });
} else {
    loadProjectsForFilter();
    loadTasks();
}
