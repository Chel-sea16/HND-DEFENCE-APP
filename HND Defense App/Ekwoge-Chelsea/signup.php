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

function json_success($msg, $extra = []){
    $response = [
        "status" => "success",
        "success" => true,
        "message" => $msg
    ];
    if (!empty($extra) && is_array($extra)) {
        $response = array_merge($response, $extra);
    }
    echo json_encode($response);
    exit();
}

session_start();
require_once 'config.php';

if(!$conn){
    json_error("Database connection failed");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error("Method not allowed");
}

if (isset($_SESSION['user_id'])) {
    json_success("Already logged in", ["redirect" => "dashboard.php"]);
}

try {
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($first_name === '' || $last_name === '' || $email === '' || $password === '' || $confirm_password === '') {
        json_error("All fields are required");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error("Invalid email format");
    }
    if (strlen($password) < 6) {
        json_error("Password must be at least 6 characters");
    }
    if ($password !== $confirm_password) {
        json_error("Passwords do not match");
    }

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if(!$checkStmt){
        json_error($conn->error);
    }
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        json_error("Email already exists");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $default_profile_picture = 'default-avatar.png';

    $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, profile_picture) VALUES (?, ?, ?, ?, ?)");
    if(!$insertStmt){
        json_error($conn->error);
    }
    $insertStmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $default_profile_picture);
    if (!$insertStmt->execute()) {
        json_error("Registration failed. Please try again.");
    }

    $user_id = $conn->insert_id;

    $projectStmt = $conn->prepare("INSERT INTO projects (user_id, project_name) VALUES (?, ?)");
    if(!$projectStmt){
        json_error($conn->error);
    }
    $defaultProjects = ["Personal", "School", "Work"];
    foreach ($defaultProjects as $proj) {
        $projectStmt->bind_param("is", $user_id, $proj);
        if (!$projectStmt->execute()) {
            json_error("User created but failed to create default projects");
        }
    }

    $_SESSION['user_id'] = $user_id;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['email'] = $email;
    $_SESSION['profile_picture'] = $default_profile_picture;
    $_SESSION['is_new_signup'] = true;

    json_success("Signup successful", ["redirect" => "dashboard.php"]);
} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    json_error("Database error. Please try again.");
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
