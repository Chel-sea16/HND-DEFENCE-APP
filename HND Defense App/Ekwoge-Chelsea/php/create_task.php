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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed - POST required');
}

try {
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $title = isset($data['title']) ? trim((string)$data['title']) : '';
    if ($title === '') {
        json_error('Title is required');
    }

    $user_id = (int)$_SESSION['user_id'];
    $description = isset($data['description']) ? trim((string)$data['description']) : null;
    $due_date = isset($data['due_date']) && $data['due_date'] !== '' ? (string)$data['due_date'] : null;
    $priority = isset($data['priority']) ? normalize_priority($data['priority']) : 'medium';
    $status = isset($data['status']) ? normalize_status($data['status']) : 'pending';
    $project_id = isset($data['project_id']) && $data['project_id'] !== '' ? (int)$data['project_id'] : null;

    if ($project_id) {
        $projectCheck = $conn->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        if(!$projectCheck){
            json_error($conn->error);
        }
        $projectCheck->bind_param('ii', $project_id, $user_id);
        $projectCheck->execute();
        $projectResult = $projectCheck->get_result();
        if ($projectResult->num_rows === 0) {
            json_error('Invalid project ID');
        }
    }

    if ($due_date) {
        $dueDateObj = DateTime::createFromFormat('Y-m-d', $due_date);
        if (!$dueDateObj || $dueDateObj->format('Y-m-d') !== $due_date) {
            json_error('Invalid due date format. Use YYYY-MM-DD');
        }
    }

    $insertQuery = "
        INSERT INTO tasks (user_id, title, description, due_date, priority, status, project_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertQuery);
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param('isssssi', $user_id, $title, $description, $due_date, $priority, $status, $project_id);

    if (!$stmt->execute()) {
        json_error('Failed to create task');
    }

    $taskId = $conn->insert_id;
    $fetchQuery = "
        SELECT t.*, p.project_name
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE t.id = ? AND t.user_id = ?
    ";
    $fetchStmt = $conn->prepare($fetchQuery);
    if(!$fetchStmt){
        json_error($conn->error);
    }
    $fetchStmt->bind_param('ii', $taskId, $user_id);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();
    $taskData = $result->fetch_assoc();

    $task = [
        'id' => (int)$taskData['id'],
        'title' => $taskData['title'],
        'description' => $taskData['description'],
        'due_date' => $taskData['due_date'],
        'priority' => display_priority($taskData['priority']),
        'status' => display_status($taskData['status']),
        'project_id' => $taskData['project_id'] ? (int)$taskData['project_id'] : null,
        'project_name' => $taskData['project_name'],
        'created_at' => $taskData['created_at']
    ];

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Task created successfully",
        "data" => ["task" => $task],
        "task" => $task
    ]);
    exit();
} catch (Exception $e) {
    error_log("create_task error: " . $e->getMessage());
    json_error('Failed to create task');
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
