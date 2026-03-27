<?php
// Test authentication setup
require_once 'config.php';

echo "<h1>Authentication System Test</h1>";
    
    // Check users table structure
    echo "<h2>Users Table Structure:</h2>";
    $result = $conn->query("DESCRIBE users");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
    
    // Check if first_name and last_name exist
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'first_name'");
    $hasFirstName = $result->num_rows > 0;
    
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'last_name'");
    $hasLastName = $result->num_rows > 0;
    
    if ($hasFirstName && $hasLastName) {
        echo "<p style='color: green;'>✓ first_name and last_name columns exist</p>";
    } else {
        echo "<p style='color: red;'>✗ first_name and last_name columns missing</p>";
    }
    
    // Show existing users
    echo "<h2>Existing Users:</h2>";
    $result = $conn->query("SELECT id, first_name, last_name, email FROM users");
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>{$row['first_name']}</td><td>{$row['last_name']}</td><td>{$row['email']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found. You can create a test user by visiting signup-page.php</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Run the database schema update: <a href='update_db_schema.php'>update_db_schema.php</a></li>";
echo "<li>Test signup: <a href='signup-page.php'>signup-page.php</a></li>";
echo "<li>Test login: <a href='login-page.php'>login-page.php</a></li>";
echo "<li>Check dashboard after login: <a href='dashboard.php'>dashboard.php</a></li>";
echo "</ol>";
?>
