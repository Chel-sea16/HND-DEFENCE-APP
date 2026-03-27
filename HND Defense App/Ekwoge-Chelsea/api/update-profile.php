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
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        
        
        // Update user information
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("sssi", $data['firstName'], $data['lastName'], $data['email'], $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['first_name'] = $data['firstName'];
            $_SESSION['last_name'] = $data['lastName'];
            $_SESSION['email'] = $data['email'];
            
            echo json_encode([
                'status' => 'success',
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
            exit();
        } else {
            json_error('Failed to update profile');
        }
        
        $conn->close();
    } catch (Exception $e) {
        json_error('Error: ' . $e->getMessage());
    }
} else {
    json_error('Invalid request method - POST required');
}
?>
