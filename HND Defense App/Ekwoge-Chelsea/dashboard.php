<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get user data
$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE id = ?");
if(!$stmt){
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Set default values if user not found
$first_name = '';
$last_name = '';
$email = '';
$profile_picture = '';

if ($user) {
    $first_name = $user['first_name'] ?? '';
    $last_name = $user['last_name'] ?? '';
    $email = $user['email'] ?? '';
    $profile_picture = $user['profile_picture'] ?? '';
}

// Generate initials for avatar if no profile picture
$initials = '';
if (empty($profile_picture)) {
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FocusTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Main Container -->
    <div class="app-container">
        <!-- Sidebar (Desktop) -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="bi bi-check2-square"></i>
                    <span>FocusTrack</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active" data-page="dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my-tasks.php" class="nav-item" data-page="my-tasks">
                    <i class="bi bi-check2-square"></i>
                    <span>My Task</span>
                </a>
                <a href="today.php" class="nav-item" data-page="today">
                    <i class="bi bi-calendar-day"></i>
                    <span>Today</span>
                </a>
                <a href="projects.php" class="nav-item" data-page="projects">
                    <i class="bi bi-kanban"></i>
                    <span>Project</span>
                </a>
                <a href="profile.php" class="nav-item" data-page="profile">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </aside>

        <!-- Top Navbar -->
        <nav class="navbar">
            <div class="navbar-actions">
                <a href="#" class="navbar-icon" title="Notifications">
                    <i class="bi bi-bell"></i>
                </a>
                <div class="user-avatar" title="User Profile">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="uploads/profile/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <span><?php echo htmlspecialchars($initials); ?></span>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="navbar-icon" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-container">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <p class="page-subtitle">Welcome <?php echo htmlspecialchars($first_name); ?>! Here's what you need to accomplish today</p>
                    </div>
                    <button class="btn btn-primary" onclick="window.location.href='create-task.php'">
                        <i class="bi bi-plus-lg"></i>
                        New Task
                    </button>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-list-task"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalTasks">0</h3>
                            <p>Total Tasks</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="completedTasks">0</h3>
                            <p>Completed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-kanban"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalProjects">0</h3>
                            <p>Projects</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="pendingTasks">0</h3>
                            <p>Pending</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Tasks -->
                <div class="card">
                    <div class="page-header">
                        <h2 class="page-title">Recent Tasks</h2>
                        <a href="my-tasks.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-right"></i>
                            View All
                        </a>
                    </div>
                    <div class="task-list" id="recentTasks">
                        <div class="loading-spinner">
                            <i class="bi bi-hourglass-split"></i>
                            <p>Loading tasks...</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="card">
                    <div class="page-header">
                        <h2 class="page-title">Recent Projects</h2>
                        <a href="projects.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-right"></i>
                            View All
                        </a>
                    </div>
                    <div class="project-list" id="recentProjects">
                        <div class="loading-spinner">
                            <i class="bi bi-hourglass-split"></i>
                            <p>Loading projects...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Mobile Header -->
        <header class="mobile-header">
            <div class="mobile-nav">
                <button class="hamburger" id="hamburger">
                    <i class="bi bi-list"></i>
                </button>
                <div class="mobile-logo">
                    <i class="bi bi-check2-square"></i>
                    <span>FocusTrack</span>
                </div>
                <div class="mobile-user">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="uploads/profile/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <span><?php echo htmlspecialchars($initials); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Mobile Bottom Navigation -->
        <nav class="mobile-nav-footer">
            <a href="dashboard.php" class="nav-item active" data-page="dashboard">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="my-tasks.php" class="nav-item" data-page="my-tasks">
                <i class="bi bi-check2-square"></i>
                <span>My Task</span>
            </a>
            <a href="today.php" class="nav-item" data-page="today">
                <i class="bi bi-calendar-day"></i>
                <span>Today</span>
            </a>
            <a href="projects.php" class="nav-item" data-page="projects">
                <i class="bi bi-kanban"></i>
                <span>Project</span>
            </a>
            <a href="profile.php" class="nav-item" data-page="profile">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
        </nav>
    </div>

    <!-- Hidden Content for Mobile Menu -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <script>
        // Load user's tasks and projects
        document.addEventListener('DOMContentLoaded', function() {
            loadUserTasks();
            loadUserProjects();
        });

        function loadUserTasks() {
            fetch('./php/fetch_user_tasks.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server response error: ' + response.status);
                    }
                    return response.json();
                })
                .then(result => {
                    console.log('Tasks response:', result);
                    if (result.success && result.data && result.data.tasks) {
                        displayTasks(result.data.tasks);
                        updateTaskStats(result.data.tasks);
                    } else {
                        throw new Error(result.message || 'Invalid response format');
                    }
                })
                .catch(error => {
                    console.error('Error loading tasks:', error);
                    document.getElementById('recentTasks').innerHTML = '<p class="error-message">Error loading tasks: ' + error.message + '</p>';
                });
        }

        function loadUserProjects() {
            fetch('./php/fetch_user_projects.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server response error: ' + response.status);
                    }
                    return response.json();
                })
                .then(result => {
                    console.log('Projects response:', result);
                    if (result.success && result.data && result.data.projects) {
                        displayProjects(result.data.projects);
                        updateProjectStats(result.data.projects);
                    } else {
                        throw new Error(result.message || 'Invalid response format');
                    }
                })
                .catch(error => {
                    console.error('Error loading projects:', error);
                    document.getElementById('recentProjects').innerHTML = '<p class="error-message">Error loading projects: ' + error.message + '</p>';
                });
        }

        function displayTasks(tasks) {
            const container = document.getElementById('recentTasks');
            if (tasks.length === 0) {
                container.innerHTML = '<p>No tasks found. <a href="create-task.php">Create your first task</a></p>';
                return;
            }

            container.innerHTML = tasks.slice(0, 5).map(task => `
                <div class="task-item">
                    <div class="task-checkbox">
                        <input type="checkbox" id="task-${task.id}" ${task.status === 'Completed' ? 'checked' : ''}>
                        <label for="task-${task.id}"></label>
                    </div>
                    <div class="task-content">
                        <h4>${task.title}</h4>
                        <p>${task.description || 'No description'}</p>
                        <div class="task-meta">
                            <span class="priority ${task.priority.toLowerCase()}">${task.priority}</span>
                            <span class="due-date">${task.due_date || 'No due date'}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function displayProjects(projects) {
            const container = document.getElementById('recentProjects');
            if (projects.length === 0) {
                container.innerHTML = '<p>No projects found. <a href="create-project.php">Create your first project</a></p>';
                return;
            }

            container.innerHTML = projects.slice(0, 3).map(project => `
                <div class="project-card">
                    <div class="project-header">
                        <h3>${project.project_name}</h3>
                        <span class="project-date">${new Date(project.created_at).toLocaleDateString()}</span>
                    </div>
                    <p>${project.description || 'No description'}</p>
                </div>
            `).join('');
        }

        function updateTaskStats(tasks) {
            const totalTasks = tasks.length;
            const completedTasks = tasks.filter(t => t.status === 'Completed').length;
            const pendingTasks = tasks.filter(t => t.status === 'Pending').length;

            document.getElementById('totalTasks').textContent = totalTasks;
            document.getElementById('completedTasks').textContent = completedTasks;
            document.getElementById('pendingTasks').textContent = pendingTasks;
        }

        function updateProjectStats(projects) {
            document.getElementById('totalProjects').textContent = projects.length;
        }
    </script>
</body>
</html>
