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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed');
}

try {
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $user_id = (int)$_SESSION['user_id'];
    $project_name = isset($data['project_name']) ? trim((string)$data['project_name']) : '';
    $description = isset($data['description']) ? trim($data['description']) : null;

    if ($project_name === '') {
        json_error("Project name is required");
    }

    $checkQuery = "SELECT id FROM projects WHERE project_name = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    if(!$checkStmt){
        json_error($conn->error);
    }
    $checkStmt->bind_param('si', $project_name, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        json_error('A project with this name already exists');
    }

    $insertQuery = "
        INSERT INTO projects (user_id, project_name, description)
        VALUES (?, ?, ?)
    ";
    
    $stmt = $conn->prepare($insertQuery);
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param('iss', $user_id, $project_name, $description);
    
    if ($stmt->execute()) {
        $projectId = $conn->insert_id;
        
        // Fetch the created project
        $fetchQuery = "
            SELECT 
                p.*,
                COUNT(t.id) as task_count,
                SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks
            FROM projects p
            LEFT JOIN tasks t ON p.id = t.project_id
            WHERE p.id = ? AND p.user_id = ?
            GROUP BY p.id
        ";
        
        $fetchStmt = $conn->prepare($fetchQuery);
        if(!$fetchStmt){
            json_error($conn->error);
        }
        $fetchStmt->bind_param('ii', $projectId, $user_id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $projectData = $result->fetch_assoc();
        
        $project = [
            'id' => (int)$projectData['id'],
            'project_name' => $projectData['project_name'],
            'description' => $projectData['description'],
            'created_at' => $projectData['created_at'],
            'task_count' => (int)$projectData['task_count'],
            'completed_tasks' => (int)$projectData['completed_tasks'],
            'completion_percentage' => $projectData['task_count'] > 0 
                ? round(($projectData['completed_tasks'] / $projectData['task_count']) * 100, 1) 
                : 0
        ];
        
        echo json_encode([
            "status" => "success",
            "success" => true,
            "message" => "Project created",
            "data" => ["project" => $project],
            "project" => $project
        ]);
        exit();
    } else {
        throw new Exception('Failed to create project');
    }
    
} catch (Exception $e) {
    error_log("Error creating project: " . $e->getMessage());
    json_error('Failed to create project: ' . $e->getMessage());
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
