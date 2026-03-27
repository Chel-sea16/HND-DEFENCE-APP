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
    <title>Create Project - TaskFlow</title>
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
                    <i class="bi bi-speedometer2"></i>
                    <span>TaskFlow</span>
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
                        <h1 class="page-title">Create Project</h1>
                        <p class="page-subtitle">Organize your tasks into projects</p>
                    </div>
                    <a href="projects.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to Projects
                    </a>
                </div>

                <!-- Create Project Form -->
                <div class="card">
                    <form id="createProjectForm">
                        <div class="form-group">
                            <label for="projectName" class="form-label">Project Name *</label>
                            <input type="text" id="projectName" name="project_name" class="input" placeholder="Enter project name" required>
                        </div>

                        <div class="form-group">
                            <label for="projectDescription" class="form-label">Description</label>
                            <textarea id="projectDescription" name="description" class="form-textarea" rows="4" placeholder="Enter project description"></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="projects.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                Create Project
                            </button>
                        </div>
                    </form>
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
                    <i class="bi bi-plus-lg"></i>
                    <span>Create Project</span>
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
        // Handle form submission
        document.getElementById('createProjectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const projectData = {
                project_name: formData.get('project_name'),
                description: formData.get('description')
            };
            
            fetch('php/create_project.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(projectData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Project created successfully!');
                    window.location.href = 'projects.php';
                } else {
                    alert('Error creating project: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating project');
            });
        });
    </script>

    <style>
        /* Form Styles */
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
            min-height: 120px;
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
            margin-top: 32px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
