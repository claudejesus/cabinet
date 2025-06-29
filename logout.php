<?php
session_start();

// Log the logout
if (isset($_SESSION["username"])) {
    error_log("User logged out: " . $_SESSION["username"]);
}

// Destroy all session data
session_destroy();

// Clear session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header("Location: index.php?message=logged_out");
exit();
?> 