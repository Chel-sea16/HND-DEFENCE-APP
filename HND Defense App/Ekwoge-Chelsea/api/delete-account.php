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
        
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete user tasks
            $stmt = $conn->prepare("DELETE FROM tasks WHERE user_id = ?");
            if(!$stmt){
                throw new Exception($conn->error);
            }
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            
            // Delete user projects
            $stmt = $conn->prepare("DELETE FROM projects WHERE user_id = ?");
            if(!$stmt){
                throw new Exception($conn->error);
            }
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            
            // Delete user profile picture if exists
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            if(!$stmt){
                throw new Exception($conn->error);
            }
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && $user['profile_picture'] && $user['profile_picture'] !== 'default-avatar.png') {
                $profilePicturePath = '../uploads/profile/' . $user['profile_picture'];
                if (file_exists($profilePicturePath)) {
                    unlink($profilePicturePath);
                }
            }
            
            // Delete user account
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            if(!$stmt){
                throw new Exception($conn->error);
            }
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Destroy session
            session_destroy();
            
            echo json_encode([
                'status' => 'success',
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            throw $e;
        }
        
        $conn->close();
    } catch (Exception $e) {
        json_error('Error: ' . $e->getMessage());
    }
} else {
    json_error('Invalid request method - POST required');
}
?>
