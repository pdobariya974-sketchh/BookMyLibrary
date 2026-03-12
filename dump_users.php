<?php
// Temporary debug page - lists `users` table contents for local troubleshooting
require_once __DIR__ . '/db.php';
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Users table dump</h2>";
echo "<p>This is a local debug page. Remove it after troubleshooting.</p>";
echo "<table border=1 cellpadding=6 cellspacing=0><tr><th>id</th><th>name</th><th>email</th><th>password</th><th>role</th></tr>";
$res = $conn->query('SELECT id, name, email, password, role FROM users');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['password']) . '</td>';
        echo '<td>' . htmlspecialchars($row['role']) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5">Query failed: ' . htmlspecialchars($conn->error) . '</td></tr>';
}
echo '</table>';
echo '<p><a href="login.php">Back to login</a></p>';
 