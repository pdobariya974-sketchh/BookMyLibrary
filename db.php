<?php
// Database connection (MySQLi)
// Update credentials if your Laragon/MySQL uses different user/password
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// set charset
$conn->set_charset('utf8mb4');

// Ensure `users` table has a `role` column (fixes imports from older backups)
// This is safe: it checks information_schema before altering.
$res = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA='" . DB_NAME . "' AND TABLE_NAME='users'");
if ($res) {
    $tbl = $res->fetch_assoc();
    if ($tbl['cnt'] > 0) {
        $res2 = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . DB_NAME . "' AND TABLE_NAME='users' AND COLUMN_NAME='role'");
        if ($res2) {
            $col = $res2->fetch_assoc();
            if ($col['cnt'] == 0) {
                // add role column with a sensible default
                $conn->query("ALTER TABLE `users` ADD COLUMN `role` ENUM('admin','librarian','user') NOT NULL DEFAULT 'user'");
                // set role for known sample accounts if present
                $conn->query("UPDATE `users` SET `role`='admin' WHERE `email`='admin@gmail.com'");
                $conn->query("UPDATE `users` SET `role`='librarian' WHERE `email`='library@gmail.com'");
                $conn->query("UPDATE `users` SET `role`='user' WHERE `email`='user@gmail.com'");
            }
        }
    }
}

// If users table is empty, insert sample accounts for local testing
$resUsers = $conn->query("SELECT COUNT(*) AS cnt FROM `users`");
if ($resUsers) {
    $u = $resUsers->fetch_assoc();
    if ($u['cnt'] == 0) {
        $conn->query("INSERT INTO `users` (`name`,`email`,`password`,`role`) VALUES
            ('Admin User','admin@gmail.com','123456','admin'),
            ('Library','library@gmail.com','123456','librarian'),
            ('Normal User','user@gmail.com','123456','user')");
    }
}
