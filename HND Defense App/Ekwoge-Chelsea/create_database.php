<?php
// Create the focustrack database if it doesn't exist
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'focustrack';

try {
    // Connect without specifying database first
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>Database Setup</h2>";
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Database '$dbname' created successfully or already exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating database: " . $conn->error . "</p>";
    }
    
    // Switch to the database
    $conn->select_db($dbname);
    
    // Create tables
    $usersTable = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255),
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $projectsTable = "
        CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#667eea',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $tasksTable = "
        CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            due_date DATE,
            priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
            status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
            project_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_project_id (project_id),
            INDEX idx_status (status),
            INDEX idx_priority (priority),
            INDEX idx_due_date (due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if ($conn->query($usersTable) === TRUE) {
        echo "<p style='color: green;'>✓ Users table created successfully</p>";
    }
    
    if ($conn->query($projectsTable) === TRUE) {
        echo "<p style='color: green;'>✓ Projects table created successfully</p>";
    }
    
    if ($conn->query($tasksTable) === TRUE) {
        echo "<p style='color: green;'>✓ Tasks table created successfully</p>";
    }
    
    // Insert sample data
    // Check if users table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert default admin user with id = 1
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (id, first_name, last_name, email, password) VALUES (1, 'Admin', 'User', 'admin@focustrack.com', ?)");
        $stmt->bind_param("s", $defaultPassword);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Default admin user created (email: admin@focustrack.com, password: admin123)</p>";
        }
    }
    
    // Check if projects table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM projects");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert sample projects
        $sampleProjects = [
            [1, 'Website Redesign', 'Complete overhaul of company website with modern design', '#667eea'],
            [1, 'Mobile App Development', 'Native iOS and Android app development', '#22c55e'],
            [1, 'Marketing Campaign', 'Q2 marketing initiatives and social media strategy', '#f59e0b']
        ];
        
        foreach ($sampleProjects as $project) {
            $stmt = $conn->prepare("INSERT INTO projects (user_id, name, description, color) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $project[0], $project[1], $project[2], $project[3]);
            $stmt->execute();
        }
        echo "<p style='color: green;'>✓ Sample projects created</p>";
    }
    
    // Check if tasks table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM tasks");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert sample tasks
        $sampleTasks = [
            [1, 'Complete project documentation', 'Write comprehensive documentation for the new features', '2024-03-20', 'high', 'in_progress', 1],
            [1, 'Review pull requests', 'Review and merge pending pull requests', '2024-03-18', 'medium', 'completed', 1],
            [1, 'Update dependencies', 'Update all npm packages to latest stable versions', '2024-03-22', 'low', 'pending', 1],
            [1, 'Design mobile mockups', 'Create UI/UX mockups for mobile application', '2024-03-25', 'high', 'pending', 2],
            [1, 'Setup CI/CD pipeline', 'Configure automated testing and deployment', '2024-03-28', 'medium', 'pending', 2],
            [1, 'Social media content', 'Create content for Q2 marketing campaign', '2024-03-19', 'medium', 'in_progress', 3]
        ];
        
        foreach ($sampleTasks as $task) {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority, status, project_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssi", $task[0], $task[1], $task[2], $task[3], $task[4], $task[5], $task[6]);
            $stmt->execute();
        }
        echo "<p style='color: green;'>✓ Sample tasks created</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>You can now access your application at: <a href='http://localhost/hnd_defence_app/'>http://localhost/hnd_defence_app/</a></p>";
    echo "<p>Login with: admin@focustrack.com / admin123</p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
