// Main JavaScript for Cherry's Todo App

// DOM Elements
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
const mainContent = document.getElementById('mainContent');

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    loadDashboardData();
    setupEventListeners();
});

// Initialize app functionality
function initializeApp() {
    // Set active navigation item based on current page
    setActiveNavItem();
    
    // Initialize mobile menu
    if (hamburger && sidebar) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }
    
    // Close mobile menu when clicking overlay
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
    }
}

// Set active navigation item
function setActiveNavItem() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href === currentPage) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

// Toggle mobile menu
function toggleMobileMenu() {
    if (sidebar) {
        sidebar.classList.toggle('active');
        mobileMenuOverlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
}

// Close mobile menu
function closeMobileMenu() {
    if (sidebar) {
        sidebar.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Setup event listeners
function setupEventListeners() {
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });
    
    // Handle navigation clicks
    const navLinks = document.querySelectorAll('.nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Close mobile menu if open
            if (window.innerWidth <= 768) {
                closeMobileMenu();
            }
        });
    });
}

// Load dashboard data
async function loadDashboardData() {
    try {
        // Load tasks
        await loadTasks();
        
        // Load projects
        await loadProjects();
        
        // Update statistics
        updateStatistics();
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        showError('Failed to load dashboard data');
    }
}

// Load tasks from server
async function loadTasks() {
    const tasksContainer = document.getElementById('recentTasks');
    if (!tasksContainer) return;
    
    try {
        const response = await fetch('./php/fetch_tasks.php');
        const data = await response.json();
        
        if (!data || data.success !== true || !Array.isArray(data.tasks)) {
            console.error('Invalid task API response:', data);
            throw new Error(data?.message || 'Invalid task data');
        }

        displayTasks(data.tasks.slice(0, 5)); // Show only 5 recent tasks
    } catch (error) {
        console.error('Error loading tasks:', error);
        // Show sample tasks for demo
        displaySampleTasks();
    }
}

// Display tasks in the dashboard
function displayTasks(tasks) {
    const tasksContainer = document.getElementById('recentTasks');
    if (!tasksContainer) return;
    
    if (tasks.length === 0) {
        tasksContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <p>No tasks yet. Create your first task!</p>
            </div>
        `;
        return;
    }
    
    tasksContainer.innerHTML = tasks.map(task => `
        <div class="task-item" data-task-id="${task.id}">
            <div class="task-checkbox ${task.completed ? 'checked' : ''}" onclick="toggleTaskComplete(${task.id})">
                ${task.completed ? '<i class="fas fa-check"></i>' : ''}
            </div>
            <div class="task-content">
                <div class="task-title">${task.title}</div>
                <div class="task-meta">
                    <span class="task-date">
                        <i class="fas fa-calendar"></i>
                        ${formatDate(task.due_date)}
                    </span>
                    ${task.priority === 'high' ? `
                        <span class="task-priority">
                            <i class="fas fa-star star"></i>
                            High Priority
                        </span>
                    ` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

// Display sample tasks for demo
function displaySampleTasks() {
    const sampleTasks = [
        {
            id: 1,
            title: 'Complete project documentation',
            completed: false,
            due_date: '2024-03-20',
            priority: 'high'
        },
        {
            id: 2,
            title: 'Review pull requests',
            completed: true,
            due_date: '2024-03-18',
            priority: 'medium'
        },
        {
            id: 3,
            title: 'Update dependencies',
            completed: false,
            due_date: '2024-03-22',
            priority: 'low'
        }
    ];
    
    displayTasks(sampleTasks);
}

// Load projects from server
async function loadProjects() {
    const projectsContainer = document.getElementById('recentProjects');
    if (!projectsContainer) return;
    
    try {
        const response = await fetch('./php/fetch_projects.php');
        const data = await response.json();
        
        if (!data || data.success !== true || !Array.isArray(data.projects)) {
            console.error('Invalid project API response:', data);
            throw new Error(data?.message || 'Invalid project data');
        }

        displayProjects(data.projects.slice(0, 3)); // Show only 3 recent projects
    } catch (error) {
        console.error('Error loading projects:', error);
        // Show sample projects for demo
        displaySampleProjects();
    }
}

// Display projects in the dashboard
function displayProjects(projects) {
    const projectsContainer = document.getElementById('recentProjects');
    if (!projectsContainer) return;
    
    if (projects.length === 0) {
        projectsContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-project-diagram"></i>
                <p>No projects yet. Create your first project!</p>
            </div>
        `;
        return;
    }
    
    projectsContainer.innerHTML = projects.map(project => `
        <div class="project-card" data-project-id="${project.id}">
            <div class="project-title">${project.name}</div>
            <div class="project-description">${project.description || 'No description available'}</div>
            <div class="project-stats">
                <span><i class="fas fa-tasks"></i> ${project.task_count || 0} tasks</span>
                <span><i class="fas fa-check-circle"></i> ${project.completed_tasks || 0} completed</span>
            </div>
        </div>
    `).join('');
}

// Display sample projects for demo
function displaySampleProjects() {
    const sampleProjects = [
        {
            id: 1,
            name: 'Website Redesign',
            description: 'Complete overhaul of company website',
            task_count: 12,
            completed_tasks: 8
        },
        {
            id: 2,
            name: 'Mobile App Development',
            description: 'Native iOS and Android app',
            task_count: 25,
            completed_tasks: 15
        },
        {
            id: 3,
            name: 'Marketing Campaign',
            description: 'Q2 marketing initiatives',
            task_count: 8,
            completed_tasks: 3
        }
    ];
    
    displayProjects(sampleProjects);
}

// Update dashboard statistics
function updateStatistics() {
    // Get all task items
    const taskItems = document.querySelectorAll('.task-item');
    let totalTasks = taskItems.length;
    let completedTasks = 0;
    let highPriorityTasks = 0;
    
    taskItems.forEach(item => {
        const checkbox = item.querySelector('.task-checkbox');
        const priority = item.querySelector('.task-priority');
        
        if (checkbox && checkbox.classList.contains('checked')) {
            completedTasks++;
        }
        
        if (priority) {
            highPriorityTasks++;
        }
    });
    
    let pendingTasks = totalTasks - completedTasks;
    
    // Update DOM elements
    updateStatElement('totalTasks', totalTasks);
    updateStatElement('completedTasks', completedTasks);
    updateStatElement('pendingTasks', pendingTasks);
    updateStatElement('highPriorityTasks', highPriorityTasks);
    
    // Update projects count
    const projectCards = document.querySelectorAll('.project-card');
    updateStatElement('totalProjects', projectCards.length);
}

// Update individual stat element
function updateStatElement(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

// Toggle task completion
async function toggleTaskComplete(taskId) {
    try {
        const response = await fetch('./php/update_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                task_id: taskId,
                action: 'toggle_complete'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Reload tasks to reflect changes
            await loadTasks();
            updateStatistics();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error toggling task:', error);
        // For demo, just toggle the UI
        const taskItem = document.querySelector(`[data-task-id="${taskId}"]`);
        if (taskItem) {
            const checkbox = taskItem.querySelector('.task-checkbox');
            checkbox.classList.toggle('checked');
            if (checkbox.classList.contains('checked')) {
                checkbox.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                checkbox.innerHTML = '';
            }
            updateStatistics();
        }
    }
}

// Format date for display
function formatDate(dateString) {
    if (!dateString) return 'No due date';
    
    const date = new Date(dateString);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    } else if (date.toDateString() === tomorrow.toDateString()) {
        return 'Tomorrow';
    } else {
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
    }
}

// Show error message
function showError(message) {
    // Create error notification
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-notification';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add to page
    document.body.appendChild(errorDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 5000);
}

// Show success message
function showSuccess(message) {
    // Create success notification
    const successDiv = document.createElement('div');
    successDiv.className = 'success-notification';
    successDiv.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add to page
    document.body.appendChild(successDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (successDiv.parentElement) {
            successDiv.remove();
        }
    }, 3000);
}

// Refresh dashboard data
async function refreshTasks() {
    const refreshBtn = document.querySelector('[onclick="refreshTasks()"]');
    if (refreshBtn) {
        const originalHTML = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        refreshBtn.disabled = true;
        
        try {
            await loadTasks();
            await loadProjects();
            updateStatistics();
            showSuccess('Dashboard refreshed successfully!');
        } catch (error) {
            console.error('Error refreshing:', error);
            showError('Failed to refresh dashboard');
        } finally {
            refreshBtn.innerHTML = originalHTML;
            refreshBtn.disabled = false;
        }
    }
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add CSS for notifications
const notificationStyles = `
    .error-notification, .success-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }
    
    .error-notification {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    
    .success-notification {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    
    .error-notification button, .success-notification button {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        margin-left: 0.5rem;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #666;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
`;

// Add notification styles to head
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);
