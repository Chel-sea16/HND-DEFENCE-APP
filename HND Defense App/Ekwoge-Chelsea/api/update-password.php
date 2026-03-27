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
        
        
        // Get current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($data['currentPassword'], $user['password'])) {
            // Update password
            $hashedPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if(!$stmt){
                json_error($conn->error);
            }
            $stmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'success' => true,
                    'message' => 'Password updated successfully'
                ]);
                exit();
            } else {
                json_error('Failed to update password');
            }
        } else {
            json_error('Current password is incorrect');
        }
        
        $conn->close();
    } catch (Exception $e) {
        json_error('Error: ' . $e->getMessage());
    }
} else {
    json_error('Invalid request method - POST required');
}
?>
