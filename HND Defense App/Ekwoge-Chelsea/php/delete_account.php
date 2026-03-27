<?php
header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

function json_error($msg){
    echo json_encode(["status"=>"error","success"=>false,"message"=>$msg]);
    exit();
}

function json_success($msg){
    echo json_encode(["status"=>"success","success"=>true,"message"=>$msg]);
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
    json_error('Invalid request method');
}

try {
    $user_id = (int)$_SESSION['user_id'];
    $conn->begin_transaction();

    $profileStmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    if(!$profileStmt){
        json_error($conn->error);
    }
    $profileStmt->bind_param("i", $user_id);
    if (!$profileStmt->execute()) {
        json_error('Failed to read profile.');
    }
    $userRow = $profileStmt->get_result()->fetch_assoc();

    $tasksStmt = $conn->prepare("DELETE FROM tasks WHERE user_id = ?");
    if(!$tasksStmt){
        json_error($conn->error);
    }
    $tasksStmt->bind_param("i", $user_id);
    if (!$tasksStmt->execute()) {
        json_error('Failed to delete user tasks.');
    }

    $projectsStmt = $conn->prepare("DELETE FROM projects WHERE user_id = ?");
    if(!$projectsStmt){
        json_error($conn->error);
    }
    $projectsStmt->bind_param("i", $user_id);
    if (!$projectsStmt->execute()) {
        json_error('Failed to delete user projects.');
    }

    $userStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if(!$userStmt){
        json_error($conn->error);
    }
    $userStmt->bind_param("i", $user_id);
    if (!$userStmt->execute()) {
        json_error('Failed to delete user account.');
    }

    $conn->commit();

    if ($userRow && !empty($userRow['profile_picture']) && $userRow['profile_picture'] !== 'default-avatar.png') {
        $profilePicturePath = '../uploads/profile/' . $userRow['profile_picture'];
        if (file_exists($profilePicturePath)) {
            @unlink($profilePicturePath);
        }
    }

    session_destroy();
    json_success('Account deleted successfully');
} catch (Exception $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    error_log("delete_account error: " . $e->getMessage());
    json_error('Error deleting account.');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
