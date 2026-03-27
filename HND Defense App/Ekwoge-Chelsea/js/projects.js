// projects.js - Load projects from API

function loadProjects() {
    fetch("./api/get_projects.php")
        .then(res => res.json())
        .then(data => {
            const projects = Array.isArray(data) ? data : (data.projects || []);
            
            const container = document.getElementById("projectsGrid");
            const noMessage = document.getElementById("noProjectsMessage");
            
            if (!container) return;
            
            if (!projects || projects.length === 0) {
                container.style.display = 'none';
                if (noMessage) noMessage.style.display = 'block';
                return;
            }
            
            container.style.display = 'grid';
            if (noMessage) noMessage.style.display = 'none';
            container.innerHTML = "";
            
            projects.forEach(project => {
                const card = document.createElement('div');
                card.className = 'project-card';
                card.setAttribute('data-project-id', project.id);
                
                const completion = project.completion_percentage || 0;
                const taskCount = project.task_count || 0;
                const completedTasks = project.completed_tasks || 0;
                
                card.innerHTML = `
                    <div class="project-header">
                        <h3>${project.project_name || project.name || 'Untitled'}</h3>
                        <div class="project-actions">
                            <button class="btn btn-sm btn-secondary" onclick="editProject(${project.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProject(${project.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <p>${project.description || 'No description'}</p>
                    <div class="project-stats">
                        <div class="stat">
                            <span class="stat-value">${taskCount}</span>
                            <span class="stat-label">Tasks</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value">${completedTasks}</span>
                            <span class="stat-label">Done</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value">${completion}%</span>
                            <span class="stat-label">Complete</span>
                        </div>
                    </div>
                    <div class="project-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${completion}%"></div>
                        </div>
                    </div>
                `;
                
                container.appendChild(card);
            });
        })
        .catch(err => {
            console.error("Error loading projects:", err);
            const container = document.getElementById("projectsGrid");
            if (container) {
                container.innerHTML = `<p>Error loading projects: ${err.message}</p>`;
            }
        });
}

function editProject(projectId) {
    const newName = prompt('Enter new project name:');
    if (newName && newName.trim()) {
        fetch('./php/update_project.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ project_id: projectId, action: 'update', project_name: newName.trim() })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadProjects();
            else alert('Error updating project');
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error updating project');
        });
    }
}

function deleteProject(projectId) {
    if (!confirm('Delete this project and all its tasks?')) return;
    
    fetch('./php/update_project.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id: projectId, action: 'delete' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) loadProjects();
        else alert('Error deleting project');
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error deleting project');
    });
}

function showCreateProjectModal() {
    const modal = document.getElementById('createProjectModal');
    if (modal) modal.style.display = 'flex';
}

function hideCreateProjectModal() {
    const modal = document.getElementById('createProjectModal');
    if (modal) modal.style.display = 'none';
    const form = document.getElementById('createProjectForm');
    if (form) form.reset();
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadProjects);
} else {
    loadProjects();
}

// Handle create project form
const projectForm = document.getElementById('createProjectForm');
if (projectForm) {
    projectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const projectData = {
            project_name: formData.get('project_name'),
            description: formData.get('description')
        };
        
        fetch('./php/create_project.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(projectData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Project created successfully');
                hideCreateProjectModal();
                loadProjects();
            } else {
                alert('Error creating project: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error creating project');
        });
    });
}

