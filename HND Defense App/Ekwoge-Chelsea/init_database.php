<?php
require_once 'config.php';

echo "<h2>Database Initialization</h2>";
    $result = initializeDatabase();
    
    if ($result) {
        echo "<p style='color: green;'>✓ Database initialized successfully</p>";
        
        // Show sample data
        echo "<h3>Sample Projects:</h3>";
        $projects_result = $conn->query("SELECT id, project_name, description FROM projects LIMIT 5");
        if ($projects_result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Description</th></tr>";
            while ($row = $projects_result->fetch_assoc()) {
                echo "<tr><td>{$row['id']}</td><td>{$row['project_name']}</td><td>{$row['description']}</td></tr>";
            }
            echo "</table>";
        }
        
        echo "<h3>Sample Tasks:</h3>";
        $tasks_result = $conn->query("SELECT id, title, description, due_date, priority, status FROM tasks LIMIT 5");
        if ($tasks_result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Title</th><th>Description</th><th>Due Date</th><th>Priority</th><th>Status</th></tr>";
            while ($row = $tasks_result->fetch_assoc()) {
                echo "<tr><td>{$row['id']}</td><td>{$row['title']}</td><td>{$row['description']}</td><td>{$row['due_date']}</td><td>{$row['priority']}</td><td>{$row['status']}</td></tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database initialization failed</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
