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
    <title>Today - FocusTrack</title>
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
                <a href="today.php" class="nav-item active" data-page="today">
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
                        <h1 class="page-title">Today</h1>
                        <p class="page-subtitle">Focus on what matters today</p>
                    </div>
                    <button class="btn btn-primary" onclick="window.location.href='create-task.php'">
                        <i class="bi bi-plus-lg"></i>
                        New Task
                    </button>
                </div>

                <!-- Today's Date -->
                <div class="today-date">
                    <div class="date-display">
                        <i class="bi bi-calendar3"></i>
                        <span id="currentDate"><?php echo date('l, F j, Y'); ?></span>
                    </div>
                    <div class="task-summary">
                        <span id="taskCount">0</span> tasks for today
                    </div>
                </div>

                <!-- Task List -->
                <div class="task-list" id="todayTasks">
                    <div class="loading-spinner">
                        <i class="bi bi-hourglass-split"></i>
                        <p>Loading today's tasks...</p>
                    </div>
                </div>

                <!-- No Tasks Message -->
                <div class="no-tasks" id="noTasksMessage" style="display: none;">
                    <div class="no-tasks-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3>No tasks for today</h3>
                    <p>You're all caught up! Enjoy your day.</p>
                    <button class="btn btn-primary" onclick="window.location.href='create-task.php'">
                        <i class="bi bi-plus-lg"></i>
                        Create Task
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
            <a href="today.php" class="nav-item active" data-page="today">
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

    <script src="js/today.js?v=2"></script>

    <style>
        /* Today Page Specific Styles */
        .today-date {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .date-display {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 600;
            color: #1A1A1A;
        }

        .date-display i {
            color: #3B82F6;
            font-size: 20px;
        }

        .task-summary {
            font-size: 14px;
            color: #6B7280;
        }

        .task-summary #taskCount {
            font-weight: 600;
            color: #3B82F6;
        }

        /* No Tasks Message */
        .no-tasks {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
        }

        .no-tasks-icon {
            font-size: 64px;
            color: #3B82F6;
            margin-bottom: 16px;
        }

        .no-tasks h3 {
            font-size: 24px;
            font-weight: 600;
            color: #1A1A1A;
            margin: 0 0 8px 0;
        }

        .no-tasks p {
            font-size: 16px;
            color: #6B7280;
            margin: 0 0 24px 0;
        }

        /* Task List Styles (reused from my-tasks) */
        .task-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }

        .task-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .task-checkbox {
            flex-shrink: 0;
        }

        .task-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .task-content {
            flex: 1;
        }

        .task-content h4 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1A1A1A;
        }

        .task-content p {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #6B7280;
        }

        .task-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .priority {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .priority.high {
            background: #FEE2E2;
            color: #DC2626;
        }

        .priority.medium {
            background: #FEF3C7;
            color: #D97706;
        }

        .priority.low {
            background: #E0E7FF;
            color: #3730A3;
        }

        .due-date {
            font-size: 12px;
            color: #DC2626;
            font-weight: 500;
        }

        .project {
            font-size: 12px;
            color: #3B82F6;
        }

        .task-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .today-date {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }

            .task-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .task-actions {
                align-self: flex-end;
            }
        }
    </style>
</body>
</html>
