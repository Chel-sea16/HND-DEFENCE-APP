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

try {
    $user_id = (int)$_SESSION['user_id'];
    $status = isset($_GET['status']) && $_GET['status'] !== '' ? normalize_status($_GET['status']) : null;
    $priority = isset($_GET['priority']) && $_GET['priority'] !== '' ? normalize_priority($_GET['priority']) : null;
    $project_id = isset($_GET['project_id']) && $_GET['project_id'] !== '' ? (int)$_GET['project_id'] : null;
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 1000;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $query = "
        SELECT t.*, p.project_name, p.color AS project_color
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE t.user_id = ?
    ";
    $params = [$user_id];
    $types = "i";

    if ($status !== null) {
        $query .= " AND t.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    if ($priority !== null) {
        $query .= " AND t.priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    if ($project_id !== null) {
        $query .= " AND t.project_id = ?";
        $params[] = $project_id;
        $types .= "i";
    }

    $query .= " ORDER BY t.id DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($query);
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($tasks as &$t) {
        $t['id'] = (int)$t['id'];
        $t['project_id'] = $t['project_id'] !== null ? (int)$t['project_id'] : null;
        $t['priority'] = display_priority($t['priority']);
        $t['status'] = display_status($t['status']);
        $t['completed'] = strtolower((string)$t['status']) === 'completed';
    }

    $countQuery = "SELECT COUNT(*) AS total FROM tasks WHERE user_id = ?";
    $countParams = [$user_id];
    $countTypes = "i";
    if ($status !== null) {
        $countQuery .= " AND status = ?";
        $countParams[] = $status;
        $countTypes .= "s";
    }
    if ($priority !== null) {
        $countQuery .= " AND priority = ?";
        $countParams[] = $priority;
        $countTypes .= "s";
    }
    if ($project_id !== null) {
        $countQuery .= " AND project_id = ?";
        $countParams[] = $project_id;
        $countTypes .= "i";
    }

    $countStmt = $conn->prepare($countQuery);
    if(!$countStmt){
        json_error($conn->error);
    }
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Tasks retrieved successfully",
        "data" => [
            "tasks" => $tasks,
            "total" => $total,
            "limit" => $limit,
            "offset" => $offset,
            "has_more" => ($offset + $limit) < $total
        ],
        "tasks" => $tasks
    ]);
    exit();
} catch (Exception $e) {
    error_log("fetch_tasks error: " . $e->getMessage());
    json_error("Failed to fetch tasks");
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
