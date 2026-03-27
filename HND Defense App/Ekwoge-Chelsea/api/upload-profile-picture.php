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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    try {
        
        
        // Handle file upload
        $file = $_FILES['profile_picture'];
        $uploadDir = '../uploads/profile/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $fileName = time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
            exit();
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
            exit();
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Update database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            if(!$stmt){
                json_error($conn->error);
            }
            $stmt->bind_param("si", $fileName, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                // Update session
                $_SESSION['profile_picture'] = $fileName;
                
                echo json_encode([
                    'status' => 'success',
                    'success' => true,
                    'message' => 'Profile picture updated successfully',
                    'data' => ['profile_picture' => $fileName],
                    'profile_picture' => $fileName
                ]);
            } else {
                json_error('Failed to update database');
            }
        } else {
            json_error('Failed to upload file');
        }
        
        $conn->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
