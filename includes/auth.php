<?php
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security error: Invalid CSRF token.");
    }

    // 2. Sanitize and Validate Input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $login_error = "Both fields are required.";
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => $login_error];
        header("Location: ../admin/login.php");
        exit;
    }

    //  Protect Against XSS
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    // 4. Prepare and Execute SQL Statement (Prevents SQL Injection)
    $stmt = $mysqli->prepare("SELECT id, password FROM admins_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    //  if admin exists
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($admin_id, $hashed_password);
        $stmt->fetch();

        // Verify password 
        if (password_verify($password, $hashed_password)) {
            // 7. Secure session creation
            session_regenerate_id(true); // Prevent session fixation

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_username'] = $username;

            unset($_SESSION['csrf_token']); // Prevent reuse of token

            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $login_error = "Incorrect password.";
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => $login_error];
            header("Location: ../admin/login.php");
            exit;
        }
    } else {
        $login_error = "Username not found.";
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => $login_error];
        header("Location: ../admin/login.php");
        exit;
    }

    $stmt->close();
}
?>
