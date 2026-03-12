<?php
require_once '../config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    flashMessage('danger', 'Invalid member ID.');
    redirect('members.php');
}

$stmt = mysqli_prepare($conn, "SELECT name FROM members WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$member) {
    flashMessage('danger', 'Member not found.');
    redirect('members.php');
}

// Prevent deletion if member has active issued books
$check = mysqli_prepare($conn, "SELECT COUNT(*) FROM issued_books WHERE member_id = ? AND status = 'issued'");
mysqli_stmt_bind_param($check, 'i', $id);
mysqli_stmt_execute($check);
mysqli_stmt_bind_result($check, $activeIssues);
mysqli_stmt_fetch($check);
mysqli_stmt_close($check);

if ($activeIssues > 0) {
    flashMessage('danger', "Cannot delete \"{$member['name']}\" – they have $activeIssues active issued book(s).");
    redirect('members.php');
}

$del = mysqli_prepare($conn, "DELETE FROM members WHERE id = ?");
mysqli_stmt_bind_param($del, 'i', $id);

if (mysqli_stmt_execute($del)) {
    flashMessage('success', "Member \"{$member['name']}\" deleted successfully.");
} else {
    flashMessage('danger', 'Failed to delete member: ' . mysqli_error($conn));
}
mysqli_stmt_close($del);

redirect('members.php');
