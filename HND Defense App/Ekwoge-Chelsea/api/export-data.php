<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../config.php';

function json_error($message){
    echo json_encode([
        "status" => "error",
        "message" => $message
    ]);
    exit();
}

if(!$conn){
    json_error("Database connection failed");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    json_error('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        
        // Get all user data for export
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Get user tasks
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ?");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        
        // Get user projects
        $stmt = $conn->prepare("SELECT * FROM projects WHERE user_id = ?");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        
        // Prepare export data
        $exportData = [
            'user' => [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'created_at' => $user['created_at']
            ],
            'tasks' => $tasks,
            'projects' => $projects,
            'export_date' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode([
            'status' => 'success',
            'success' => true,
            'data' => $exportData
        ]);
        exit();
        
        $conn->close();
    } catch (Exception $e) {
        json_error('Error: ' . $e->getMessage());
    }
} else {
    json_error('Invalid request method - POST required');
}
?>
