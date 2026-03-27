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

function normalize_priority($priority) {
    $priority = strtolower(trim((string)$priority));
    if ($priority === 'high') return 'high';
    if ($priority === 'low') return 'low';
    return 'medium';
}

function normalize_status($status) {
    $status = strtolower(trim((string)$status));
    if ($status === 'completed' || $status === 'complete' || $status === 'done') return 'completed';
    if ($status === 'in_progress' || $status === 'in progress') return 'in_progress';
    return 'pending';
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

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'], true)) {
    json_error('Method not allowed');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $data = is_array($input) ? $input : $_POST;
    $taskId = isset($data['task_id']) ? (int)$data['task_id'] : 0;
    $action = isset($data['action']) ? (string)$data['action'] : 'update';
    $user_id = (int)$_SESSION['user_id'];

    if ($taskId <= 0) {
        json_error('Task ID is required');
    }

    $checkStmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
    if(!$checkStmt){
        json_error($conn->error);
    }
    $checkStmt->bind_param("ii", $taskId, $user_id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        json_error('Task not found');
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("ii", $taskId, $user_id);
        if (!$stmt->execute()) {
            json_error('Failed to delete task');
        }
        echo json_encode(["status"=>"success","success"=>true,"message"=>"Task deleted successfully"]);
        exit();
    }

    if ($action === 'toggle_complete') {
        $stmt = $conn->prepare("
            UPDATE tasks
            SET status = CASE WHEN status = 'completed' THEN 'pending' ELSE 'completed' END
            WHERE id = ? AND user_id = ?
        ");
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param("ii", $taskId, $user_id);
        if (!$stmt->execute()) {
            json_error('Failed to update task status');
        }
    } else {
        $fields = [];
        $params = [];
        $types = "";

        if (array_key_exists('title', $data)) {
            $fields[] = "title = ?";
            $params[] = trim((string)$data['title']);
            $types .= "s";
        }
        if (array_key_exists('description', $data)) {
            $fields[] = "description = ?";
            $params[] = trim((string)$data['description']);
            $types .= "s";
        }
        if (array_key_exists('due_date', $data)) {
            if ($data['due_date'] === '' || $data['due_date'] === null) {
                $fields[] = "due_date = NULL";
            } else {
                $fields[] = "due_date = ?";
                $params[] = (string)$data['due_date'];
                $types .= "s";
            }
        }
        if (array_key_exists('priority', $data)) {
            $fields[] = "priority = ?";
            $params[] = normalize_priority($data['priority']);
            $types .= "s";
        }
        if (array_key_exists('status', $data)) {
            $fields[] = "status = ?";
            $params[] = normalize_status($data['status']);
            $types .= "s";
        }
        if (array_key_exists('project_id', $data)) {
            if ($data['project_id'] === '' || $data['project_id'] === null) {
                $fields[] = "project_id = NULL";
            } else {
                $project_id = (int)$data['project_id'];
                $projectStmt = $conn->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
                if(!$projectStmt){
                    json_error($conn->error);
                }
                $projectStmt->bind_param("ii", $project_id, $user_id);
                $projectStmt->execute();
                if ($projectStmt->get_result()->num_rows === 0) {
                    json_error("Invalid project ID");
                }
                $fields[] = "project_id = ?";
                $params[] = $project_id;
                $types .= "i";
            }
        }

        if (empty($fields)) {
            json_error('No fields to update');
        }

        $sql = "UPDATE tasks SET " . implode(", ", $fields) . " WHERE id = ? AND user_id = ?";
        $params[] = $taskId;
        $params[] = $user_id;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        if(!$stmt){
            json_error($conn->error);
        }
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            json_error('Failed to update task');
        }
    }

    $fetchStmt = $conn->prepare("
        SELECT t.*, p.project_name
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE t.id = ? AND t.user_id = ?
    ");
    if(!$fetchStmt){
        json_error($conn->error);
    }
    $fetchStmt->bind_param("ii", $taskId, $user_id);
    $fetchStmt->execute();
    $task = $fetchStmt->get_result()->fetch_assoc();

    $payload = [
        "id" => (int)$task['id'],
        "title" => $task['title'],
        "description" => $task['description'],
        "due_date" => $task['due_date'],
        "priority" => display_priority($task['priority']),
        "status" => display_status($task['status']),
        "project_id" => $task['project_id'] !== null ? (int)$task['project_id'] : null,
        "project_name" => $task['project_name'],
        "created_at" => $task['created_at']
    ];

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Task updated successfully",
        "data" => ["task" => $payload],
        "task" => $payload
    ]);
    exit();
} catch (Exception $e) {
    error_log("update_task error: " . $e->getMessage());
    json_error('Failed to update task');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
