<!-- Profile Sidebar Component -->
<div class="profile-avatar-section">
    <?php if (!empty($user['profile_picture'])): ?>
        <img src="uploads/profile/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" class="profile-avatar">
    <?php else: ?>
        <div class="profile-avatar-placeholder"><?php echo htmlspecialchars($initials); ?></div>
    <?php endif; ?>
    <button class="btn-change-avatar" onclick="document.getElementById('profilePictureInput').click()">
        <i class="bi bi-camera"></i>
    </button>
</div>

<div class="profile-info">
    <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
    <p class="profile-role">Product Designer</p>
    <div class="profile-stats">
        <div class="stat">
            <span class="stat-number">12</span>
            <span class="stat-label">Tasks</span>
        </div>
        <div class="stat">
            <span class="stat-number">3</span>
            <span class="stat-label">Projects</span>
        </div>
    </div>
</div>

<nav class="profile-nav">
    <a href="#personal" class="nav-item active" data-section="personal">
        <i class="bi bi-person"></i>
        Personal Information
    </a>
    <a href="#security" class="nav-item" data-section="security">
        <i class="bi bi-shield-check"></i>
        Security
    </a>
    <a href="#preferences" class="nav-item" data-section="preferences">
        <i class="bi bi-gear"></i>
        Preferences
    </a>
    <a href="#danger" class="nav-item" data-section="danger">
        <i class="bi bi-exclamation-triangle"></i>
        Danger Zone
    </a>
</nav>
