<?php
/**
 * Database Connection Configuration
 * Cherry's Todo App
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'focustrack');

// Create database connection
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database if it doesn't exist
        $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $conn->select_db(DB_NAME);
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");

        // Ensure projects uses project_name internally for compatibility
        $tablesResult = $conn->query("SHOW TABLES LIKE 'projects'");
        if ($tablesResult && $tablesResult->num_rows > 0) {
            $nameColumn = $conn->query("SHOW COLUMNS FROM projects LIKE 'name'");
            $projectNameColumn = $conn->query("SHOW COLUMNS FROM projects LIKE 'project_name'");

            if ($nameColumn && $nameColumn->num_rows > 0 && $projectNameColumn && $projectNameColumn->num_rows === 0) {
                $conn->query("ALTER TABLE projects CHANGE name project_name VARCHAR(255) NOT NULL");
            }
        }

        return $conn;
    } catch (Exception $e) {
        // Log error
        error_log("Database connection error: " . $e->getMessage());
        
        // Return error response for API calls
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please run create_database.php first.'
        ]);
        exit;
    }
}

// Initialize database tables
function initializeDatabase() {
    
    
    try {
        // Create projects table
        $projectsTable = "
            CREATE TABLE IF NOT EXISTS projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                project_name VARCHAR(255) NOT NULL,
                description TEXT,
                color VARCHAR(7) DEFAULT '#667eea',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        // Create tasks table
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
        
        // Create users table (optional for future authentication)
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
        
        // Execute table creation
        $conn->query($projectsTable);
        $conn->query($tasksTable);
        $conn->query($usersTable);
        
        // Insert sample data if tables are empty
        insertSampleData($conn);
        
        return true;
    } catch (Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    } finally {
        $conn->close();
    }
}

// Insert sample data for demonstration
function insertSampleData($conn) {
    try {
        // First, ensure we have a default user (user_id = 1)
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE id = 1");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insert default admin user with id = 1
            $stmt = $conn->prepare("INSERT INTO users (id, first_name, last_name, email, password) VALUES (1, 'Admin', 'User', 'admin@focustrack.com', ?)");
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt->bind_param("s", $defaultPassword);
            $stmt->execute();
        }
        
        // Check if projects table is empty
        $result = $conn->query("SELECT COUNT(*) as count FROM projects");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insert sample projects with user_id = 1 (default admin user)
            $sampleProjects = [
                [1, 'Website Redesign', 'Complete overhaul of company website with modern design', '#667eea'],
                [1, 'Mobile App Development', 'Native iOS and Android app development', '#22c55e'],
                [1, 'Marketing Campaign', 'Q2 marketing initiatives and social media strategy', '#f59e0b']
            ];
            
            foreach ($sampleProjects as $project) {
                $stmt = $conn->prepare("INSERT INTO projects (user_id, project_name, description, color) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $project[0], $project[1], $project[2], $project[3]);
                $stmt->execute();
            }
        }
        
        // Check if tasks table is empty
        $result = $conn->query("SELECT COUNT(*) as count FROM tasks");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insert sample tasks with user_id = 1
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
        }
    } catch (Exception $e) {
        error_log("Sample data insertion error: " . $e->getMessage());
    }
}

// Utility function to send JSON response
function sendJsonResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// Utility function to validate input
function validateInput($data, $required = []) {
    $errors = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    
    return $errors;
}

// Utility function to get request data (JSON from POST/PUT/DELETE request body)
function getRequestData() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}

// Helper functions for status and priority normalization
function normalizeStatus($status) {
    $status = strtolower(trim($status));
    
    // Map variations to standard values
    $mapping = [
        'pending' => 'Pending',
        'in progress' => 'In Progress',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'complete' => 'Completed',
        'done' => 'Completed'
    ];
    
    return isset($mapping[$status]) ? $mapping[$status] : 'Pending';
}

function normalizePriority($priority) {
    $priority = strtolower(trim($priority));
    
    // Map variations to standard values
    $mapping = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High'
    ];
    
    return isset($mapping[$priority]) ? $mapping[$priority] : 'Medium';
}

// Initialize database on first run
if (!function_exists('getConnection')) {
    initializeDatabase();
}
?>
