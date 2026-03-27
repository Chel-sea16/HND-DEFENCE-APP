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

function display_priority($priority) {
    return ucfirst(strtolower((string)$priority));
}

function display_status($status) {
    $status = strtolower((string)$status);
    if ($status === 'in_progress') return 'In Progress';
    return ucfirst($status);
}

session_start();
require_once '../config.php';

if(!$conn){
    json_error("Database connection failed");
}

if (!isset($_SESSION['user_id'])) {
    json_error('Unauthorized');
}

try {
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT t.id, t.title, t.description, t.due_date, t.priority, t.status, t.created_at, t.project_id, p.project_name
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
    ");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($tasks as &$t) {
        $t['id'] = (int)$t['id'];
        $t['project_id'] = $t['project_id'] !== null ? (int)$t['project_id'] : null;
        $t['priority'] = display_priority($t['priority']);
        $t['status'] = display_status($t['status']);
    }

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Tasks fetched successfully",
        "data" => ["tasks" => $tasks],
        "tasks" => $tasks
    ]);
    exit();
} catch (Exception $e) {
    error_log("fetch_user_tasks error: " . $e->getMessage());
    json_error("Failed to fetch tasks");
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
