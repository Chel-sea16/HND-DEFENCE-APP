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

// Generate initials for avatar fallback
$initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
if ($initials === '') {
    $initials = 'U';
}
$has_profile_picture = !empty($profile_picture);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - FocusTrack</title>
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
                <a href="projects.php" class="nav-item" data-page="projects">
                    <i class="bi bi-kanban"></i>
                    <span>Project</span>
                </a>
                <a href="profile.php" class="nav-item active" data-page="profile">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </aside>

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
                    <?php if ($has_profile_picture): ?>
                        <img src="uploads/profile/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile">
                    <?php else: ?>
                        <span><?php echo htmlspecialchars($initials); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <div class="profile-content">
                <div class="profile-header">
                    <h1>Profile</h1>
                    <p>Manage your account and preferences</p>
                </div>

                <!-- Profile Overview -->
                <div class="profile-overview">
                    <div class="profile-avatar-section">
                        <div class="profile-avatar" id="avatarContainer">
                            <span id="avatarInitials" class="avatar-initials<?php echo $has_profile_picture ? ' avatar-hidden' : ''; ?>">
                                <?php echo htmlspecialchars($initials); ?>
                            </span>
                            <img
                                id="profilePreview"
                                <?php if ($has_profile_picture): ?>
                                    src="uploads/profile/<?php echo htmlspecialchars($profile_picture); ?>"
                                <?php endif; ?>
                                alt="Profile"
                                class="<?php echo $has_profile_picture ? '' : 'avatar-hidden'; ?>"
                            >
                            <label for="imageUpload" class="camera-icon">
                                <i class="bi bi-camera"></i>
                            </label>
                            <input type="file" id="imageUpload" name="profile_image" hidden>
                        </div>
                        <div class="profile-info">
                            <h2 class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h2>
                            <p class="user-email"><?php echo htmlspecialchars($email); ?></p>
                            <div class="user-stats">
                                <span class="stat-badge">
                                    <i class="bi bi-list-check"></i>
                                    <span id="userTaskCount">0</span>
                                    Tasks
                                </span>
                                <span class="stat-badge">
                                    <i class="bi bi-kanban"></i>
                                    <span id="userProjectCount">0</span>
                                    Projects
                                </span>
                                <span class="stat-badge">
                                    <i class="bi bi-check-circle"></i>
                                    <span id="userCompletionRate">0%</span>
                                    Completed
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Sections -->
                <div class="profile-sections">
                    <!-- Personal Information -->
                    <div class="profile-section card">
                        <div class="section-header" onclick="toggleSection('personalInfo')">
                            <h3>
                                <i class="bi bi-person"></i>
                                Personal Information
                            </h3>
                            <i class="bi bi-chevron-down toggle-icon" id="personalInfoIcon"></i>
                        </div>
                        <div class="section-content" id="personalInfo">
                            <form class="profile-form" id="personalInfoForm">
                                <!-- Row 1: First Name | Last Name -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstName" class="form-label">First Name</label>
                                        <input type="text" id="firstName" class="input" value="<?php echo htmlspecialchars($first_name); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName" class="form-label">Last Name</label>
                                        <input type="text" id="lastName" class="input" value="<?php echo htmlspecialchars($last_name); ?>" readonly>
                                    </div>
                                </div>
                                
                                <!-- Row 2: Email Address | Phone Number -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" id="email" class="input" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" id="phone" class="input" placeholder="+1 (555) 123-4567">
                                    </div>
                                </div>
                                
                                <!-- Row 3: Bio (full width) -->
                                <div class="form-group">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea id="bio" class="form-textarea" rows="4" placeholder="Tell us about yourself...">Passionate about productivity and organization. Love helping teams achieve their goals through effective task management.</textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Preferences -->
                    <div class="profile-section card">
                        <div class="section-header" onclick="toggleSection('preferences')">
                            <h3>
                                <i class="bi bi-gear"></i>
                                Preferences
                            </h3>
                            <i class="bi bi-chevron-down toggle-icon" id="preferencesIcon"></i>
                        </div>
                        <div class="section-content" id="preferences">
                            <form class="profile-form">
                                <div class="form-group">
                                    <label for="language" class="form-label">Language</label>
                                    <select id="language" class="form-select">
                                        <option value="en" selected>English</option>
                                        <option value="es">Español</option>
                                        <option value="fr">Français</option>
                                        <option value="de">Deutsch</option>
                                        <option value="zh">中文</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select id="timezone" class="form-select">
                                        <option value="UTC">UTC</option>
                                        <option value="America/New_York" selected>Eastern Time</option>
                                        <option value="America/Chicago">Central Time</option>
                                        <option value="America/Denver">Mountain Time</option>
                                        <option value="America/Los_Angeles">Pacific Time</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="dateFormat" class="form-label">Date Format</label>
                                    <select id="dateFormat" class="form-select">
                                        <option value="MM/DD/YYYY" selected>MM/DD/YYYY</option>
                                        <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                        <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <input type="checkbox" id="emailNotifications" checked>
                                        Email Notifications
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <input type="checkbox" id="darkMode">
                                        Dark Mode
                                    </label>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i>
                                        Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="profile-section card">
                        <div class="section-header" onclick="toggleSection('security')">
                            <h3>
                                <i class="bi bi-shield-check"></i>
                                Security
                            </h3>
                            <i class="bi bi-chevron-down toggle-icon" id="securityIcon"></i>
                        </div>
                        <div class="section-content" id="security">
                            <form class="profile-form" id="securityForm">
                                <div class="form-group">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" id="currentPassword" class="input" placeholder="Enter current password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('currentPassword')">
                                            <i class="bi bi-eye" id="currentPassword-toggle-icon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <div class="input-wrapper">
                                            <input type="password" id="newPassword" class="input" placeholder="Enter new password">
                                            <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                                                <i class="bi bi-eye" id="newPassword-toggle-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                                        <div class="input-wrapper">
                                            <input type="password" id="confirmPassword" class="input" placeholder="Confirm new password">
                                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                                <i class="bi bi-eye" id="confirmPassword-toggle-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <input type="checkbox" id="twoFactor">
                                        Enable Two-Factor Authentication
                                    </label>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-shield-check"></i>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

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
            <a href="profile.php" class="nav-item active" data-page="profile">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
        </nav>
    </div>

    <!-- Hidden Content for Mobile Menu -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <script src="js/main.js"></script>
    <script>
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = document.getElementById(sectionId + 'Icon');
            
            if (section.style.display === 'none') {
                section.style.display = 'block';
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            } else {
                section.style.display = 'none';
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        }

        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + '-toggle-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Profile picture upload and preview
        const uploadInput = document.getElementById('imageUpload');
        const previewImage = document.getElementById('profilePreview');
        const avatarInitials = document.getElementById('avatarInitials');

        if (uploadInput && previewImage && avatarInitials) {
            uploadInput.addEventListener('change', function(event){
                const file = event.target.files[0];
                
                if(file){
                    if (!file.type || !file.type.startsWith('image/')) {
                        alert('Please choose a valid image file.');
                        return;
                    }

                    const reader = new FileReader();
                    
                    reader.onload = function(e){
                        previewImage.src = e.target.result;
                        previewImage.classList.remove('avatar-hidden');
                        avatarInitials.classList.add('avatar-hidden');
                    }
                    
                    reader.readAsDataURL(file);
                    
                    // Upload to server
                    const formData = new FormData();
                    formData.append('profile_picture', file);
                    
                    fetch('./php/update_profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the image source to the uploaded file
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alert('Error: ' + data.message);
                            // Reset to original image on error
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error uploading profile picture');
                        window.location.reload();
                    });
                }
            });
        }

        // Security form submission
        document.getElementById('securityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            const formData = new FormData();
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);
            
            fetch('./php/update_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password updated successfully');
                    this.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating password');
            });
        });

        // Load user stats
        document.addEventListener('DOMContentLoaded', function() {
            loadUserStats();
        });

        function loadUserStats() {
            fetch('./php/fetch_user_tasks.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tasks = data.tasks;
                        const totalTasks = tasks.length;
                        const completedTasks = tasks.filter(t => t.status === 'Completed').length;
                        const completionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
                        
                        document.getElementById('userTaskCount').textContent = totalTasks;
                        document.getElementById('userCompletionRate').textContent = completionRate + '%';
                    }
                })
                .catch(error => console.error('Error loading stats:', error));
            
            fetch('./php/fetch_user_projects.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('userProjectCount').textContent = data.projects.length;
                    }
                })
                .catch(error => console.error('Error loading stats:', error));
        }
    </script>

    <style>
        /* Profile Page Specific Styles */
        .profile-content {
            padding: 32px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .profile-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 8px 0;
        }

        .profile-header p {
            color: #6B7280;
            font-size: 16px;
            margin: 0;
        }

        .profile-overview {
            display: flex;
            gap: 32px;
            align-items: center;
            margin-bottom: 32px;
            padding: 32px;
            background: white;
            border-radius: 12px;
            border: 1px solid #E5E7EB;
        }

        .profile-avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .avatar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .profile-avatar{
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid #3B82F6;
            position: relative;
            overflow: hidden;
            margin: 0 auto;
            background: #F3F4F6;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            position: absolute;
            inset: 0;
        }

        .avatar-initials {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            background: #3B82F6;
            color: #FFFFFF;
            border-radius: 50%;
            position: absolute;
            inset: 0;
        }

        .avatar-hidden {
            display: none !important;
        }

        .camera-icon{
            position:absolute;
            bottom:0;
            right:0;
            background:#3B82F6;
            color:white;
            width:35px;
            height:35px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition: background-color 0.2s ease;
            border: 2px solid #FFFFFF;
            z-index: 2;
        }

        .camera-icon:hover{
            background:#2563EB;
        }

        .camera-icon i{
            font-size: 14px;
        }

        .profile-info {
            text-align: center;
        }

        .user-name {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 8px 0;
        }

        .user-email {
            color: #6B7280;
            font-size: 16px;
            margin: 0 0 16px 0;
        }

        .user-stats {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .stat-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #F3F4F6;
            border-radius: 8px;
            font-size: 14px;
            color: #374151;
        }

        .stat-badge i {
            font-size: 16px;
        }

        /* Profile Sections */
        .profile-sections {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .profile-section.card {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            overflow: hidden;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            background: #F9FAFB;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .section-header:hover {
            background: #F3F4F6;
        }

        .section-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header i {
            font-size: 20px;
            color: #3B82F6;
        }

        .toggle-icon {
            font-size: 16px;
            color: #6B7280;
            transition: transform 0.2s ease;
        }

        .section-content {
            padding: 24px;
            display: block;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
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

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            color: #9CA3AF;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #3B82F6;
        }

        .password-toggle i {
            font-size: 18px;
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

        .form-select {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
        }

        .form-select:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-content {
                padding: 20px;
            }

            .profile-overview {
                flex-direction: column;
                padding: 24px;
                gap: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .form-actions {
                flex-direction: column;
                gap: 12px;
            }

            .user-stats {
                flex-direction: column;
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .profile-content {
                padding: 16px;
            }

            .input,
            .form-select {
                font-size: 16px; /* Prevent zoom on iOS */
            }

            .form-textarea {
                font-size: 16px;
            }
        }
    </style>
</body>
</html>
