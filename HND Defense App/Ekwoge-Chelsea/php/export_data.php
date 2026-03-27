<?php
header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

function json_error($msg){
    echo json_encode(["status"=>"error","success"=>false,"message"=>$msg]);
    exit();
}

function json_success($msg, $data){
    echo json_encode(["status"=>"success","success"=>true,"message"=>$msg,"data"=>$data]);
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
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    json_error('Invalid request method');
}

try {
    $user_id = (int)$_SESSION['user_id'];

    $userStmt = $conn->prepare("
        SELECT first_name, last_name, email, phone, bio, profile_picture, created_at
        FROM users
        WHERE id = ?
    ");
    if(!$userStmt){
        json_error($conn->error);
    }
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();

    $tasksStmt = $conn->prepare("
        SELECT id, title, description, due_date, priority, status, project_id, created_at, updated_at
        FROM tasks
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    if(!$tasksStmt){
        json_error($conn->error);
    }
    $tasksStmt->bind_param("i", $user_id);
    $tasksStmt->execute();
    $tasks = $tasksStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $projectsStmt = $conn->prepare("
        SELECT id, project_name, description, color, created_at, updated_at
        FROM projects
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    if(!$projectsStmt){
        json_error($conn->error);
    }
    $projectsStmt->bind_param("i", $user_id);
    $projectsStmt->execute();
    $projects = $projectsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    json_success('Data exported successfully', [
        'export_date' => date('Y-m-d H:i:s'),
        'user' => $user,
        'tasks' => $tasks,
        'projects' => $projects
    ]);
} catch (Exception $e) {
    error_log("export_data error: " . $e->getMessage());
    json_error('Error exporting data.');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
