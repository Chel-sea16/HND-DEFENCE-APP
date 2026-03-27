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

try {
    $user_id = (int)$_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 1000;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $stmt = $conn->prepare("
        SELECT p.*,
               COUNT(t.id) AS task_count,
               SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks
        FROM projects p
        LEFT JOIN tasks t ON t.project_id = p.id AND t.user_id = p.user_id
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.id DESC
        LIMIT ? OFFSET ?
    ");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($projects as &$p) {
        $p['id'] = (int)$p['id'];
        $p['task_count'] = (int)$p['task_count'];
        $p['completed_tasks'] = (int)$p['completed_tasks'];
        $p['name'] = $p['project_name'];
        $p['completion_percentage'] = $p['task_count'] > 0 ? round(($p['completed_tasks'] / $p['task_count']) * 100, 1) : 0;
    }

    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM projects WHERE user_id = ?");
    if(!$countStmt){
        json_error($conn->error);
    }
    $countStmt->bind_param("i", $user_id);
    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Projects retrieved successfully",
        "data" => [
            "projects" => $projects,
            "total" => $total,
            "limit" => $limit,
            "offset" => $offset,
            "has_more" => ($offset + $limit) < $total
        ],
        "projects" => $projects
    ]);
    exit();
} catch (Exception $e) {
    error_log("fetch_projects error: " . $e->getMessage());
    json_error("Failed to fetch projects");
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
