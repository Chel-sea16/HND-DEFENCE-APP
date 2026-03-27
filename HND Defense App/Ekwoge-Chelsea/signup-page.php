<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - TaskFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <?php
    session_start();
    
    // Check if user is already logged in
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit();
    }
    ?>
    
    <div class="auth-container">
        <!-- Left Panel -->
        <div class="auth-left">
            <div class="auth-content">
                <div class="auth-badge">Start your journey today</div>
                <h1>Organize your life.</h1>
                <h2>Achieve more every day.</h2>
                <p>Join thousands of users who have transformed their productivity with our intuitive task management system. Start organizing your tasks, tracking your progress, and achieving your goals today.</p>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Join 10,000+ productive users</h4>
                        <p>Be part of a community that values organization, efficiency, and achieving meaningful goals every day.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Panel -->
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="auth-header">
                    <h1>Create your account</h1>
                    <p>Sign up to start organizing your tasks and boosting your productivity.</p>
                </div>
                
                <?php
                if (isset($_SESSION['signup_errors'])) {
                    echo '<div class="error-messages">';
                    foreach ($_SESSION['signup_errors'] as $error) {
                        echo '<div class="error-message">' . htmlspecialchars($error) . '</div>';
                    }
                    echo '</div>';
                    unset($_SESSION['signup_errors']);
                }
                ?>
                
                <form id="signupForm" class="auth-form" method="POST" action="signup.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <div class="input-wrapper">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" id="first_name" name="first_name" class="input" placeholder="Enter your first name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <div class="input-wrapper">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" id="last_name" name="last_name" class="input" placeholder="Enter your last name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="input" placeholder="Enter your email address" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="input" placeholder="Create a password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="bi bi-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="input" placeholder="Confirm your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="bi bi-eye" id="confirm_password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="bi bi-person-plus"></i>
                            Sign Up
                        </button>
                    </div>
                    
                    <div class="form-links">
                        <span>Already have an account? <a href="login-page.php" class="link">Sign In</a></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            // Allow normal form submission to PHP
            // No need for fetch since we're using a regular form submission
        });
    </script>

    <style>
        /* Auth Body */
        .auth-body {
            margin: 0;
            padding: 0;
            font-family: Inter, system-ui, sans-serif;
            background: #F5F7FB;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Auth Container */
        .auth-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Left Panel */
        .auth-left {
            width: 55%;
            background: #3B82F6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(37, 99, 235, 0.9) 100%);
            z-index: 1;
        }

        .auth-content {
            position: relative;
            z-index: 2;
            max-width: 500px;
            text-align: center;
        }

        .auth-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 16px;
            backdrop-filter: blur(10px);
        }

        .auth-left h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .auth-left h2 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 16px;
            line-height: 1.3;
        }

        .auth-left p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
            opacity: 0.9;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 24px;
            text-align: left;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .feature-icon i {
            font-size: 24px;
            color: white;
        }

        .feature-text h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .feature-text p {
            font-size: 14px;
            line-height: 1.5;
            opacity: 0.8;
            margin: 0;
        }

        /* Right Panel */
        .auth-right {
            width: 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            background: white;
        }

        .auth-form-container {
            width: 100%;
            max-width: 440px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .auth-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 6px;
        }

        .auth-header p {
            color: #6B7280;
            font-size: 16px;
            margin: 0;
        }

        /* Form Styles */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input {
            width: 100%;
            height: 48px;
            padding: 0 16px 0 44px;
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

        .input-icon {
            position: absolute;
            left: 16px;
            color: #9CA3AF;
            font-size: 18px;
            z-index: 1;
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

        .form-actions {
            margin-top: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 8px;
        }

        .btn-primary {
            background: #3B82F6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563EB;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-full {
            width: 100%;
        }

        .form-links {
            text-align: center;
            margin-top: 24px;
        }

        .form-links span {
            color: #6B7280;
            font-size: 14px;
        }

        .link {
            color: #3B82F6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .link:hover {
            color: #2563EB;
        }

        .error-messages {
            margin-bottom: 20px;
        }

        .error-message {
            background: #FEE2E2;
            color: #DC2626;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 8px;
            border: 1px solid #FCA5A5;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .auth-left {
                width: 50%;
            }
            
            .auth-right {
                width: 50%;
            }
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
            }
            
            .auth-left {
                width: 100%;
                min-height: auto;
                padding: 40px 20px;
            }
            
            .auth-right {
                width: 100%;
                padding: 32px 20px;
            }
            
            .auth-left h1 {
                font-size: 32px;
            }
            
            .auth-left h2 {
                font-size: 24px;
            }
            
            .auth-header h1 {
                font-size: 28px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .auth-left {
                padding: 32px 16px;
            }
            
            .auth-right {
                padding: 24px 16px;
            }
            
            .auth-left h1 {
                font-size: 28px;
            }
            
            .auth-left h2 {
                font-size: 20px;
            }
            
            .auth-header h1 {
                font-size: 24px;
            }
            
            .input {
                font-size: 16px; /* Prevent zoom on iOS */
            }
        }
    </style>
</body>
</html>
