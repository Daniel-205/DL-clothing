<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dlclothing');
define('DB_CHARSET', 'utf8mb4');


$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);


$mysqli->set_charset(DB_CHARSET);


if ($mysqli->connect_error) {
    error_log("Database connection failed: " . $mysqli->connect_error);
    die("Database connection problem. Please try again later.");
}
?>