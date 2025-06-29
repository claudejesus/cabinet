<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function get_users() {
    $file = __DIR__ . '/users.json';
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $users = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            return [];
        }
        return $users ?: [];
    }
    // Default admin if file missing
    return [
        ["username" => "admin", "password" => "admin123", "role" => "admin"]
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    
    // Basic validation
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=empty_fields");
        exit();
    }
    
    $users = get_users();
    $login_successful = false;
    
    foreach ($users as $user) {
        if ($user["username"] === $username && $user["password"] === $password) {
            $_SESSION["logged_in"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $user["role"];
            $_SESSION["login_time"] = time();
            
            // Log successful login
            error_log("Successful login for user: " . $username);
            
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Log failed login attempt
    error_log("Failed login attempt for username: " . $username);
    
    header("Location: index.php?error=invalid_credentials");
    exit();
}
?>