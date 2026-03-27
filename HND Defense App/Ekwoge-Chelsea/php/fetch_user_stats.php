<?php
header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

function json_error($msg){
    echo json_encode(["status"=>"error","success"=>false,"message"=>$msg]);
    exit();
}

session_start();
require_once '../config.php';

if(!$conn){
    json_error("Database connection failed");
}
if (!isset($_SESSION['user_id'])) {
    json_error('User not authenticated');
}

try {
    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tasks WHERE user_id = ?");
    if(!$stmt){ json_error($conn->error); }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $totalTasks = (int)$stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS completed FROM tasks WHERE user_id = ? AND status = 'completed'");
    if(!$stmt){ json_error($conn->error); }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $completedTasks = (int)$stmt->get_result()->fetch_assoc()['completed'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS pending FROM tasks WHERE user_id = ? AND status = 'pending'");
    if(!$stmt){ json_error($conn->error); }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pendingTasks = (int)$stmt->get_result()->fetch_assoc()['pending'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS in_progress FROM tasks WHERE user_id = ? AND status = 'in_progress'");
    if(!$stmt){ json_error($conn->error); }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $inProgressTasks = (int)$stmt->get_result()->fetch_assoc()['in_progress'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS today FROM tasks WHERE user_id = ? AND DATE(due_date) = CURDATE()");
    if(!$stmt){ json_error($conn->error); }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $todayTasks = (int)$stmt->get_result()->fetch_assoc()['today'];

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Stats fetched successfully",
        "data" => [
            "totalTasks" => $totalTasks,
            "completedTasks" => $completedTasks,
            "pendingTasks" => $pendingTasks,
            "inProgressTasks" => $inProgressTasks,
            "todayTasks" => $todayTasks
        ]
    ]);
    exit();
} catch (Exception $e) {
    error_log("fetch_user_stats error: " . $e->getMessage());
    json_error('Database error');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
