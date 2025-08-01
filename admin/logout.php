<?php


require_once '../includes/dbconfig.php'; 
require_once '../includes/functions.php'; 

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If admin is logged in, handle logout process
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {

    // Optional: Log admin activity
    // if (isset($_SESSION['admin_id'])) {
    //     logAdminActivity($_SESSION['admin_id'], 'logout', 'Admin logged out successfully');
    // }

    // Store flash message before destroying session
    $flashType = 'success';
    $flashMessage = 'You have been successfully logged out.';

    // Destroy session data
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params["path"], $params["domain"], 
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // Start a new session just to store flash message
    // session_start();
    // setFlashMessage($flashType, $flashMessage);
}


header("Location: login.php");
exit;
?>