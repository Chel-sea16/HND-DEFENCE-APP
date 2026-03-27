<?php
header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

function json_error($msg){
    echo json_encode([
        "status"=>"error",
        "success"=>false,
        "message"=>$msg
    ]);
    exit();
}

function json_success($msg, $extra = []){
    $response = ["status"=>"success","success"=>true,"message"=>$msg];
    if (!empty($extra) && is_array($extra)) {
        $response = array_merge($response, $extra);
    }
    echo json_encode($response);
    exit();
}

session_start();
require_once '../config.php';

if(!$conn){
    json_error("Database connection failed");
}

if (!isset($_SESSION['user_id'])) {
    json_error('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed');
}

try {
    $user_id = (int)$_SESSION['user_id'];

    if (isset($_FILES['profile_picture'])) {
        $file = $_FILES['profile_picture'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            json_error('No file uploaded or upload error.');
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes, true)) {
            json_error('Invalid file type. Only JPG, JPEG, PNG, GIF and WebP are allowed.');
        }
        if ((int)$file['size'] > 5 * 1024 * 1024) {
            json_error('File size too large. Maximum size is 5MB.');
        }

        $uploadDir = '../uploads/profile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            json_error('Failed to upload file.');
        }

        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("si", $filename, $user_id);
        if (!$stmt->execute()) {
            json_error('Failed to update profile picture in database.');
        }

        $_SESSION['profile_picture'] = $filename;
        json_success('Profile picture updated successfully', ["data" => ["profile_picture" => $filename], "profile_picture" => $filename]);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $data = is_array($input) ? $input : $_POST;

    $firstName = trim((string)($data['first_name'] ?? $data['firstName'] ?? ''));
    $lastName = trim((string)($data['last_name'] ?? $data['lastName'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $phone = trim((string)($data['phone'] ?? ''));
    $bio = trim((string)($data['bio'] ?? ''));

    if ($firstName === '' || $lastName === '' || $email === '') {
        json_error('First name, last name, and email are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('Invalid email format.');
    }

    $emailCheck = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    if(!$emailCheck){
        json_error($conn->error);
    }
    $emailCheck->bind_param("si", $email, $user_id);
    $emailCheck->execute();
    if ($emailCheck->get_result()->num_rows > 0) {
        json_error('Email is already in use by another account.');
    }

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, bio = ? WHERE id = ?");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("sssssi", $firstName, $lastName, $email, $phone, $bio, $user_id);
    if (!$stmt->execute()) {
        json_error('Failed to update profile.');
    }

    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;
    $_SESSION['email'] = $email;

    json_success('Profile updated successfully', [
        "data" => [
            "user" => [
                "first_name" => $firstName,
                "last_name" => $lastName,
                "email" => $email,
                "phone" => $phone,
                "bio" => $bio
            ]
        ]
    ]);
} catch (Exception $e) {
    error_log("update_profile error: " . $e->getMessage());
    json_error('Error updating profile.');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
