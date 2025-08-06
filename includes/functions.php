<?php



// Prevent direct access
if (!defined('DB_HOST')) {
    die('Direct access not permitted');
}

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email address
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate secure password hash
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in as admin
 * @return bool
 */
function is_admin_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Check if user is logged in as regular user
 * @return bool
 */
function is_user_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

/**
 * Redirect to login if not authenticated
 * @param string $user_type 'admin' or 'user'
 * @param string $redirect_url
 */
function require_login($user_type = 'user', $redirect_url = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $is_logged_in = false;
    $default_redirect = 'login.php';
    
    if ($user_type === 'admin') {
        $is_logged_in = is_admin_logged_in();
        $default_redirect = 'admin/admin-login.php';
    } else {
        $is_logged_in = is_user_logged_in();
    }
    
    if (!$is_logged_in) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Access denied. Please log in.'
        ];
        
        $redirect = $redirect_url ?: $default_redirect;
        header("Location: $redirect");
        exit;
    }
}

/**
 * Set flash message
 * @param string $type 'success', 'error', 'warning', 'info'
 * @param string $message
 */
function set_flash_message($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function get_flash_message() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Display flash message HTML
 * @return string
 */
function display_flash_message() {
    $flash = get_flash_message();
    if (!$flash) {
        return '';
    }
    
    $type = htmlspecialchars($flash['type']);
    $message = htmlspecialchars($flash['message']);
    
    $class_map = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $css_class = $class_map[$type] ?? 'alert-info';
    
    return "
    <div class='alert $css_class alert-dismissible fade show' role='alert'>
        $message
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

/**
 * Format price for display
 * @param float $price
 * @param string $currency
 * @return string
 */
function format_price($price, $currency = 'GHS') {
    return $currency . ' ' . number_format($price, 2);
}

/**
 * Generate random string
 * @param int $length
 * @return string
 */
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Upload file with validation
 * @param array $file $_FILES array element
 * @param string $upload_dir
 * @param array $allowed_types
 * @param int $max_size Maximum file size in bytes
 * @return array ['success' => bool, 'message' => string, 'filename' => string]
 */
function upload_file($file, $upload_dir = 'uploads/', $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded', 'filename' => ''];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error', 'filename' => ''];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large', 'filename' => ''];
    }
    
    // Get file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check allowed file types
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'File type not allowed', 'filename' => ''];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'message' => 'File uploaded successfully', 'filename' => $upload_path];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file', 'filename' => ''];
    }
}

/**
 * Delete file safely
 * @param string $filepath
 * @return bool
 */
function delete_file($filepath) {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get product by ID
 * @param mysqli $connection
 * @param int $product_id
 * @return array|null
 */
function get_product_by_id($connection, $product_id) {
    $stmt = $connection->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get all products with optional filters
 * @param mysqli $connection
 * @param array $filters
 * @return array
 */
function get_products($connection, $filters = []) {
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filters['category'])) {
        $sql .= " AND category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    if (!empty($filters['min_price'])) {
        $sql .= " AND price >= ?";
        $params[] = $filters['min_price'];
        $types .= "d";
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND price <= ?";
        $params[] = $filters['max_price'];
        $types .= "d";
    }
    
    $sql .= " ORDER BY id DESC";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = $filters['limit'];
        $types .= "i";
    }
    
    $stmt = $connection->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Log activity
 * @param string $action
 * @param string $details
 * @param string $user_type
 */
function log_activity($action, $details = '', $user_type = 'user') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'details' => $details,
        'user_type' => $user_type,
        'session_id' => session_id(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // You can implement database logging or file logging here
    error_log("Activity Log: " . json_encode($log_entry));
}

/**
 * Clean old sessions (call this periodically)
 * @param mysqli $connection
 */
function clean_old_sessions($connection) {
    // This assumes you have a sessions table
    $sql = "DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $connection->query($sql);
}

/**
 * Generate CSRF token
 * @return string
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Escape output for HTML
 * @param string $string
 * @return string
 */
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect with message
 * @param string $url
 * @param string $message
 * @param string $type
 */
function redirect_with_message($url, $message, $type = 'info') {
    set_flash_message($type, $message);
    header("Location: $url");
    exit;
}

// to resize image
function resizeImage($sourcePath, $destPath, $maxWidth = 500, $quality = 80) {
    $info = getimagesize($sourcePath);
    if (!$info) return false;

    list($width, $height) = $info;
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $srcImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $srcImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $srcImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    // Calculate new dimensions
    if ($width <= $maxWidth) {
        $newWidth = $width;
        $newHeight = $height;
    } else {
        $newWidth = $maxWidth;
        $newHeight = intval($height * ($maxWidth / $width));
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // For PNG/WebP with transparency
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }

    imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, 
        $newWidth, $newHeight, $width, $height);

    // Save image
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($newImage, $destPath, $quality);
            break;
        case 'image/png':
            imagepng($newImage, $destPath, 8); // 0 (no compression) - 9
            break;
        case 'image/webp':
            imagewebp($newImage, $destPath, $quality);
            break;
    }

    imagedestroy($srcImage);
    imagedestroy($newImage);

    return true;
}



/**
 * Alias for set_flash_message (for backward compatibility)
 * @param string $type
 * @param string $message
 */
function setFlashMessage($type, $message) {
    return set_flash_message($type, $message);
}

/**
 * Alias for get_flash_message (for backward compatibility)
 * @return array|null
 */
function getFlashMessage() {
    return get_flash_message();
}

/**
 * Check if the current request is an AJAX request
 * @return bool
 */
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}


function get_or_create_visitor_token() {
    if (!isset($_COOKIE['visitor_token'])) {
        $token = bin2hex(random_bytes(32));
        setcookie('visitor_token', $token, time() + (86400 * 30), "/", "", false, true); 
        $_COOKIE['visitor_token'] = $token; 
    }
    return $_COOKIE['visitor_token'];
}



/**
 * Standardized JSON response for AJAX requests
 */
function send_json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Calculate cart totals
 */
function calculate_cart_totals($cart_items) {
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $tax_rate = 0.00; // 0% tax
    $shipping = $subtotal > 0 ? 15 : 0;
    $tax = $subtotal * $tax_rate;
    $grand_total = $subtotal + $tax + $shipping;
    
    return [
        'subtotal' => $subtotal,
        'tax' => $tax,
        'shipping' => $shipping,
        'grandTotal' => $grand_total
    ];
}


?>