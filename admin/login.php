<?php 
require_once '../includes/dbconfig.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Start session centrally (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: ../admin/dashboard.php");
    exit;
}

// CSRF Token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Flash message function (if not defined elsewhere)
// function getFlashMessage() {
//     if (!isset($_SESSION['flash_message'])) return null;

//     $msg = $_SESSION['flash_message'];
//     unset($_SESSION['flash_message']); // Clear after showing
//     return [
//         'type' => htmlspecialchars($msg['type']),
//         'message' => htmlspecialchars($msg['message'])
//     ];
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./Styles/admin.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-header h2 {
            color: #2a2a42;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
            transition: border 0.3s ease;
        }
        .form-group input:focus {
            border-color: #4a66d8;
            outline: none;
        }
        .form-group .icon {
            position: absolute;
            right: 15px;
            top: 40px;
            color: #aaa;
        }
        .btn-submit {
            background: #1C1C1C;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-submit:hover {
            background: #333;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            color: #4a66d8;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-danger {
            background-color: #fee;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        .alert-success {
            background-color: #efe;
            color: #2ecc71;
            border-left: 4px solid #2ecc71;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Admin Login</h2>
            <p>Enter your credentials to access the admin panel</p>
        </div>

        <?php
            $flashMessage = getFlashMessage();

            if ($flashMessage && is_array($flashMessage)): 
                $alertClass = ($flashMessage['type'] === 'error') ? 'alert-danger' : 'alert-success';
            ?>
                <div class="alert <?= $alertClass ?>">
                    <?= htmlspecialchars($flashMessage['message']) ?>
                </div>
        <?php elseif (is_string($flashMessage)):  ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($flashMessage) ?>
                </div>
        <?php endif; ?>

        <form action="../includes/auth.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required autofocus>
                <span class="icon"><i class="fas fa-user"></i></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <span class="icon"><i class="fas fa-lock"></i></span>
            </div>

            <button type="submit" class="btn-submit">Log In</button>
        </form>

        <a href="../public/index.php" class="back-link">← Back to Main Site</a>
    </div>
</body>
</html>
