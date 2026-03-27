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

session_start();
require_once '../config.php';

if(!$conn){
    json_error("Database connection failed");
}

if (!isset($_SESSION['user_id'])) {
    json_error('Unauthorized');
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'], true)) {
    json_error('Method not allowed');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $data = is_array($input) ? $input : $_POST;
    $user_id = (int)$_SESSION['user_id'];
    $action = isset($data['action']) ? (string)$data['action'] : 'update';
    $project_id = isset($data['project_id']) ? (int)$data['project_id'] : 0;

    if ($project_id <= 0) {
        json_error('Project ID is required');
    }

    $checkStmt = $conn->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    if(!$checkStmt){
        json_error($conn->error);
    }
    $checkStmt->bind_param("ii", $project_id, $user_id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        json_error('Project not found');
    }

    if ($action === 'delete') {
        $deleteTasksStmt = $conn->prepare("DELETE FROM tasks WHERE project_id = ? AND user_id = ?");
        if(!$deleteTasksStmt){
            json_error($conn->error);
        }
        $deleteTasksStmt->bind_param("ii", $project_id, $user_id);
        $deleteTasksStmt->execute();

        $deleteProjectStmt = $conn->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        if(!$deleteProjectStmt){
            json_error($conn->error);
        }
        $deleteProjectStmt->bind_param("ii", $project_id, $user_id);
        if (!$deleteProjectStmt->execute()) {
            json_error('Failed to delete project');
        }

        echo json_encode(["status"=>"success","success"=>true,"message"=>"Project deleted successfully"]);
        exit();
    }

    $fields = [];
    $params = [];
    $types = "";

    if (array_key_exists('project_name', $data)) {
        $name = trim((string)$data['project_name']);
        if ($name === '') {
            json_error('Project name cannot be empty');
        }
        $fields[] = "project_name = ?";
        $params[] = $name;
        $types .= "s";
    }
    if (array_key_exists('description', $data)) {
        $fields[] = "description = ?";
        $params[] = trim((string)$data['description']);
        $types .= "s";
    }

    if (empty($fields)) {
        json_error('No fields to update');
    }

    $sql = "UPDATE projects SET " . implode(", ", $fields) . " WHERE id = ? AND user_id = ?";
    $params[] = $project_id;
    $params[] = $user_id;
    $types .= "ii";

    $updateStmt = $conn->prepare($sql);
    if(!$updateStmt){
        json_error($conn->error);
    }
    $updateStmt->bind_param($types, ...$params);
    if (!$updateStmt->execute()) {
        json_error('Failed to update project');
    }

    $fetchStmt = $conn->prepare("
        SELECT p.*,
               COUNT(t.id) AS task_count,
               SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks
        FROM projects p
        LEFT JOIN tasks t ON t.project_id = p.id AND t.user_id = p.user_id
        WHERE p.id = ? AND p.user_id = ?
        GROUP BY p.id
    ");
    if(!$fetchStmt){
        json_error($conn->error);
    }
    $fetchStmt->bind_param("ii", $project_id, $user_id);
    $fetchStmt->execute();
    $project = $fetchStmt->get_result()->fetch_assoc();

    $payload = [
        "id" => (int)$project['id'],
        "project_name" => $project['project_name'],
        "name" => $project['project_name'],
        "description" => $project['description'],
        "created_at" => $project['created_at'],
        "task_count" => (int)$project['task_count'],
        "completed_tasks" => (int)$project['completed_tasks'],
        "completion_percentage" => ((int)$project['task_count'] > 0) ? round(((int)$project['completed_tasks'] / (int)$project['task_count']) * 100, 1) : 0
    ];

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Project updated successfully",
        "data" => ["project" => $payload],
        "project" => $payload
    ]);
    exit();
} catch (Exception $e) {
    error_log("update_project error: " . $e->getMessage());
    json_error('Failed to update project');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
