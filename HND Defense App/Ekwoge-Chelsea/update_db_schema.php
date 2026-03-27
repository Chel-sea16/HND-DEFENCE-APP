<?php
require_once 'php/db.php';

try {
    $conn = getConnection();
    
    // Check if first_name and last_name columns exist
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'first_name'");
    if ($result->num_rows == 0) {
        // Add first_name and last_name columns
        $conn->query("ALTER TABLE users ADD COLUMN first_name VARCHAR(255) AFTER id");
        $conn->query("ALTER TABLE users ADD COLUMN last_name VARCHAR(255) AFTER first_name");
        
        // Update existing records to split name into first_name and last_name
        $result = $conn->query("SELECT id, name FROM users WHERE name IS NOT NULL AND name != ''");
        while ($row = $result->fetch_assoc()) {
            $nameParts = explode(' ', $row['name'], 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
            $stmt->bind_param("ssi", $firstName, $lastName, $row['id']);
            $stmt->execute();
        }
        
        // Optionally remove the old name column after migration
        // $conn->query("ALTER TABLE users DROP COLUMN name");
        
        echo "<p style='color: green;'>✓ Added first_name and last_name columns</p>";
    } else {
        echo "<p style='color: blue;'>⚠ first_name and last_name columns already exist</p>";
    }
    
    // Show updated table structure
    echo "<h3>Updated Users Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    
    $result = $conn->query("DESCRIBE users");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
