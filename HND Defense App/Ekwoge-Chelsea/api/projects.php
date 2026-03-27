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

try {
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, project_name, description, color, created_at FROM projects WHERE user_id = ? ORDER BY created_at DESC");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'success' => true,
        'data' => $projects,
        'projects' => $projects
    ]);
    
    $conn->close();
} catch (Exception $e) {
    json_error('Error loading projects: ' . $e->getMessage());
}
?>
