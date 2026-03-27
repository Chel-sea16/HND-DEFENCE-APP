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
    $stmt = $conn->prepare("
        SELECT p.*,
               COUNT(t.id) AS task_count,
               SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks
        FROM projects p
        LEFT JOIN tasks t ON t.project_id = p.id AND t.user_id = p.user_id
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($projects as &$p) {
        $p['id'] = (int)$p['id'];
        $p['task_count'] = (int)$p['task_count'];
        $p['completed_tasks'] = (int)$p['completed_tasks'];
        $p['name'] = $p['project_name'];
        $p['completion_percentage'] = $p['task_count'] > 0 ? round(($p['completed_tasks'] / $p['task_count']) * 100, 1) : 0;
    }

    echo json_encode([
        "status" => "success",
        "success" => true,
        "message" => "Projects fetched successfully",
        "data" => ["projects" => $projects],
        "projects" => $projects
    ]);
    exit();
} catch (Exception $e) {
    error_log("fetch_user_projects error: " . $e->getMessage());
    json_error("Failed to fetch projects");
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
