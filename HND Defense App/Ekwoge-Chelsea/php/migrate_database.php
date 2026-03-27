<?php
/**
 * Database Migration Script
 * Fixes existing tables by adding missing user_id columns and correcting column names
 * Run this in browser: http://localhost/Cherry's%20HND%20defence%20App/php/migrate_database.php
 */

require_once '../config.php';

header('Content-Type: text/plain');

echo "=== FocusTrack Database Migration ===\n\n";

try {
    
    
    // 1. Check and fix users table
    echo "1. Checking users table...\n";
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
    if ($result->num_rows > 0) {
        echo "   - Found 'name' column, migrating to first_name/last_name...\n";
        $conn->query("ALTER TABLE users ADD COLUMN first_name VARCHAR(100) AFTER id");
        $conn->query("ALTER TABLE users ADD COLUMN last_name VARCHAR(100) AFTER first_name");
        $conn->query("UPDATE users SET first_name = SUBSTRING_INDEX(name, ' ', 1), last_name = SUBSTRING_INDEX(name, ' ', -1)");
        $conn->query("ALTER TABLE users DROP COLUMN name");
        echo "   - Migrated successfully\n";
    } else {
        echo "   - Users table OK\n";
    }
    
    // 2. Add user_id to projects if missing
    echo "\n2. Checking projects table...\n";
    $result = $conn->query("SHOW COLUMNS FROM projects LIKE 'user_id'");
    if ($result->num_rows == 0) {
        echo "   - Adding user_id column...\n";
        $conn->query("ALTER TABLE projects ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER id");
        $conn->query("ALTER TABLE projects ADD INDEX idx_user_id (user_id)");
        echo "   - Added user_id column\n";
    } else {
        echo "   - user_id column exists\n";
    }
    
    // Fix project_name column
    $result = $conn->query("SHOW COLUMNS FROM projects LIKE 'name'");
    if ($result->num_rows > 0) {
        echo "   - Renaming 'name' to 'project_name'...\n";
        $conn->query("ALTER TABLE projects CHANGE name project_name VARCHAR(255) NOT NULL");
        echo "   - Renamed successfully\n";
    }
    
    // 3. Add user_id to tasks if missing
    echo "\n3. Checking tasks table...\n";
    $result = $conn->query("SHOW COLUMNS FROM tasks LIKE 'user_id'");
    if ($result->num_rows == 0) {
        echo "   - Adding user_id column...\n";
        $conn->query("ALTER TABLE tasks ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER id");
        $conn->query("ALTER TABLE tasks ADD INDEX idx_user_id (user_id)");
        echo "   - Added user_id column\n";
    } else {
        echo "   - user_id column exists\n";
    }
    
    // 4. Ensure default user exists
    echo "\n4. Checking default user...\n";
    $result = $conn->query("SELECT id FROM users WHERE id = 1");
    if ($result->num_rows == 0) {
        echo "   - Creating default user...\n";
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (id, first_name, last_name, email, password) VALUES (1, 'Admin', 'User', 'admin@focustrack.com', ?)");
        $stmt->bind_param("s", $password);
        $stmt->execute();
        echo "   - Default user created (email: admin@focustrack.com, password: admin123)\n";
    } else {
        echo "   - Default user exists\n";
    }
    
    // 5. Update orphaned records
    echo "\n5. Updating orphaned records...\n";
    $conn->query("UPDATE projects SET user_id = 1 WHERE user_id = 0 OR user_id IS NULL");
    $conn->query("UPDATE tasks SET user_id = 1 WHERE user_id = 0 OR user_id IS NULL");
    echo "   - Updated orphaned records\n";
    
    echo "\n=== Migration Complete ===\n";
    echo "\nNext steps:\n";
    echo "1. Login with: admin@focustrack.com / admin123\n";
    echo "2. Or register a new account\n";
    echo "3. Refresh the dashboard to see tasks and projects\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nIf tables don't exist yet, please visit the dashboard first to initialize the database.\n";
}
?>
