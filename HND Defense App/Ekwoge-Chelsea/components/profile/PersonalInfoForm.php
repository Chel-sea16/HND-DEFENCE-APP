<!-- Personal Information Form Component -->
<div class="content-section active" id="personal">
    <h3>Personal Information</h3>
    <form class="profile-form" id="personalInfoForm">
        <div class="form-row">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-input">
            </div>
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-input">
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-input">
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="+1 (555) 123-4567" class="form-input">
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..." class="form-input"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-outline">Cancel</button>
        </div>
    </form>
</div>

<!-- Security Section -->
<div class="content-section" id="security">
    <h3>Security</h3>
    <form class="profile-form" id="securityForm">
        <div class="form-group">
            <label for="currentPassword">Current Password</label>
            <input type="password" id="currentPassword" name="currentPassword" class="form-input">
        </div>
        <div class="form-group">
            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="newPassword" class="form-input">
        </div>
        <div class="form-group">
            <label for="confirmPassword">Confirm New Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Password</button>
            <button type="button" class="btn btn-outline">Cancel</button>
        </div>
    </form>
</div>
