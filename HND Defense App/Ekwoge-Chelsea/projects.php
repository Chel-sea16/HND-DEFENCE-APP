<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-page.php");
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

// Generate initials for avatar if no profile picture
$initials = '';
if (empty($user['profile_picture'])) {
    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - FocusTrack</title>
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
                <a href="dashboard.php" class="nav-item" data-page="dashboard">
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
                <a href="projects.php" class="nav-item active" data-page="projects">
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
                        <h1 class="page-title">Projects</h1>
                        <p class="page-subtitle">Organize your tasks into projects</p>
                    </div>
                    <button class="btn btn-primary" onclick="showCreateProjectModal()">
                        <i class="bi bi-plus-lg"></i>
                        New Project
                    </button>
                </div>

                <!-- Projects Grid -->
                <div class="projects-grid" id="projectsGrid">
                    <div class="loading-spinner">
                        <i class="bi bi-hourglass-split"></i>
                        <p>Loading projects...</p>
                    </div>
                </div>

                <!-- No Projects Message -->
                <div class="no-projects" id="noProjectsMessage" style="display: none;">
                    <div class="no-projects-icon">
                        <i class="bi bi-kanban"></i>
                    </div>
                    <h3>No projects yet</h3>
                    <p>Create your first project to start organizing your tasks.</p>
                    <button class="btn btn-primary" onclick="showCreateProjectModal()">
                        <i class="bi bi-plus-lg"></i>
                        Create Project
                    </button>
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
            <a href="dashboard.php" class="nav-item" data-page="dashboard">
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
            <a href="projects.php" class="nav-item active" data-page="projects">
                <i class="bi bi-kanban"></i>
                <span>Project</span>
            </a>
            <a href="profile.php" class="nav-item" data-page="profile">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
        </nav>
    </div>

    <!-- Create Project Modal -->
    <div class="modal" id="createProjectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Project</h3>
                <button class="modal-close" onclick="hideCreateProjectModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form id="createProjectForm">
                <div class="form-group">
                    <label for="projectName" class="form-label">Project Name</label>
                    <input type="text" id="projectName" name="project_name" class="input" placeholder="Enter project name" required>
                </div>
                <div class="form-group">
                    <label for="projectDescription" class="form-label">Description</label>
                    <textarea id="projectDescription" name="description" class="form-textarea" rows="4" placeholder="Enter project description"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideCreateProjectModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Content for Mobile Menu -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <script src="js/projects.js?v=2"></script>

    <style>
        /* Projects Grid */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        /* Project Card */
        .project-card {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 24px;
            transition: all 0.2s ease;
        }

        .project-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .project-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1A1A1A;
            margin: 0;
            flex: 1;
        }

        .project-actions {
            display: flex;
            gap: 8px;
        }

        .project-card p {
            color: #6B7280;
            font-size: 14px;
            margin: 0 0 16px 0;
            line-height: 1.5;
        }

        .project-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            display: block;
            font-size: 20px;
            font-weight: 600;
            color: #1A1A1A;
        }

        .stat-label {
            font-size: 12px;
            color: #6B7280;
        }

        .project-progress {
            margin-bottom: 16px;
        }

        .progress-bar {
            height: 8px;
            background: #F3F4F6;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #3B82F6;
            transition: width 0.3s ease;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 32px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1A1A1A;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6B7280;
            cursor: pointer;
            padding: 4px;
        }

        .modal-close:hover {
            color: #1A1A1A;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px 16px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            resize: vertical;
            font-size: 14px;
            font-family: Inter, system-ui, sans-serif;
            line-height: 1.5;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        /* No Projects Message */
        .no-projects {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-top: 24px;
        }

        .no-projects-icon {
            font-size: 64px;
            color: #3B82F6;
            margin-bottom: 16px;
        }

        .no-projects h3 {
            font-size: 24px;
            font-weight: 600;
            color: #1A1A1A;
            margin: 0 0 8px 0;
        }

        .no-projects p {
            font-size: 16px;
            color: #6B7280;
            margin: 0 0 24px 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .projects-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .modal-content {
                padding: 24px;
                margin: 16px;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
