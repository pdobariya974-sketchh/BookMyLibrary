<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

function redirect($url) {
    // Only allow relative paths to prevent open-redirect attacks
    if (preg_match('#^https?://#i', $url)) {
        $url = 'index.php';
    }
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('../index.php');
    }
}

function sanitize($conn, $value) {
    return mysqli_real_escape_string($conn, trim($value));
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
