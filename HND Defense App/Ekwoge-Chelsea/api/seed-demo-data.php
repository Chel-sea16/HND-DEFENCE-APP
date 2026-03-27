<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../config.php';

function json_error($message){
    echo json_encode([
        "status" => "error",
        "message" => $message
    ]);
    exit();
}

if(!$conn){
    json_error("Database connection failed");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    json_error('Unauthorized');
}

try {
    
    $user_id = $_SESSION['user_id'];
    
    // Check if user already has tasks
    $stmt = $conn->prepare("SELECT COUNT(*) as task_count FROM tasks WHERE user_id = ?");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task_count = $result->fetch_assoc()['task_count'];
    
    if ($task_count == 0) {
        $projectStmt = $conn->prepare("SELECT id FROM projects WHERE user_id = ? ORDER BY id ASC LIMIT 1");
        if(!$projectStmt){
            json_error($conn->error);
        }
        $projectStmt->bind_param("i", $user_id);
        $projectStmt->execute();
        $projectRow = $projectStmt->get_result()->fetch_assoc();
        $defaultProjectId = $projectRow ? (int)$projectRow['id'] : null;

        if ($defaultProjectId === null) {
            $createProjectStmt = $conn->prepare("INSERT INTO projects (user_id, project_name) VALUES (?, ?)");
            if(!$createProjectStmt){
                json_error($conn->error);
            }
            $projectName = "Personal";
            $createProjectStmt->bind_param("is", $user_id, $projectName);
            if (!$createProjectStmt->execute()) {
                json_error("Failed to create default project");
            }
            $defaultProjectId = $conn->insert_id;
        }

        $demo_tasks = [
            ['Finalize onboarding checklist', 'Complete all onboarding tasks and documentation', '2026-03-25', 'high', 'pending', $defaultProjectId],
            ['Update sprint planning board', 'Review and update the sprint board with new tasks', '2026-03-26', 'medium', 'in_progress', $defaultProjectId],
            ['Submit design review notes', 'Compile and submit design review feedback', '2026-03-27', 'medium', 'pending', $defaultProjectId]
        ];
        
        foreach ($demo_tasks as $task) {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority, status, project_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if(!$stmt){
                json_error($conn->error);
            }
            $stmt->bind_param("isssssi", $user_id, $task[0], $task[1], $task[2], $task[3], $task[4], $task[5]);
            if (!$stmt->execute()) {
                json_error("Failed to seed demo tasks");
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'success' => true,
            'message' => 'Demo data seeded successfully',
            'tasks_seeded' => count($demo_tasks)
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'success' => true,
            'message' => 'User already has tasks',
            'existing_tasks' => $task_count
        ]);
    }
    
    $conn->close();
} catch (Exception $e) {
    json_error('Error seeding demo data: ' . $e->getMessage());
}
?>
