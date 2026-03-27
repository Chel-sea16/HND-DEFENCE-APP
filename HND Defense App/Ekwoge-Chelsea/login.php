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

if (isset($_SESSION['user_id'])) {
    json_success("Already logged in", ["redirect" => "dashboard.php"]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error("Method not allowed");
}

try {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        json_error("Email and password are required");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error("Invalid email format");
    }

    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, profile_picture FROM users WHERE email = ?");
    if(!$stmt){
        json_error($conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        json_error("Invalid email or password");
    }

    $user = $result->fetch_assoc();
    if (!password_verify($password, $user['password'])) {
        json_error("Invalid email or password");
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['profile_picture'] = $user['profile_picture'];

    json_success("Login successful", ["redirect" => "dashboard.php"]);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    json_error("Database error. Please try again.");
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
