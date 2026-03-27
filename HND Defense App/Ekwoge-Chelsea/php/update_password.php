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

function json_success($msg){
    echo json_encode([
        "status"=>"success",
        "success"=>true,
        "message"=>$msg
    ]);
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
    $input = json_decode(file_get_contents('php://input'), true);
    $data = is_array($input) ? $input : $_POST;

    $current_password = (string)($data['current_password'] ?? $data['currentPassword'] ?? '');
    $new_password = (string)($data['new_password'] ?? $data['newPassword'] ?? '');
    $confirm_password = (string)($data['confirm_password'] ?? $data['confirmPassword'] ?? $new_password);

    if (trim($current_password) === '' || trim($new_password) === '' || trim($confirm_password) === '') {
        json_error('All password fields are required');
    }
    if ($new_password !== $confirm_password) {
        json_error('Passwords do not match');
    }
    if (strlen($new_password) < 6) {
        json_error('Password must be at least 6 characters');
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 1) {
        json_error('User not found');
    }

    $user = $result->fetch_assoc();
    if (!password_verify($current_password, $user['password'])) {
        json_error('Current password is incorrect');
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if(!$updateStmt){
        json_error($conn->error);
    }
    $updateStmt->bind_param("si", $hashed_password, $user_id);
    if (!$updateStmt->execute()) {
        json_error('Failed to update password');
    }

    json_success('Password updated successfully');
} catch (Exception $e) {
    error_log("update_password error: " . $e->getMessage());
    json_error('Error updating password.');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
