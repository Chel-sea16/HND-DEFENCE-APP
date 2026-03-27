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

include '../config.php';
session_start();

if(!$conn){
    json_error("Database connection failed");
}

try {
    if (!isset($_SESSION['user_id'])) {
        json_error("Unauthorized");
    }

    $user_id = (int)$_SESSION['user_id'];
    $sql = "SELECT * FROM projects WHERE user_id = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $projects = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "success" => true,
        "data" => $projects,
        "projects" => $projects
    ]);
} catch (Exception $e) {
    json_error('Database error: ' . $e->getMessage());
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
