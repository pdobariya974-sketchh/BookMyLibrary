<?php
require_once '../config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    flashMessage('danger', 'Invalid book ID.');
    redirect('books.php');
}

// Check if book exists
$stmt = mysqli_prepare($conn, "SELECT title FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$book) {
    flashMessage('danger', 'Book not found.');
    redirect('books.php');
}

// Check for active issued copies before deleting
$check = mysqli_prepare($conn, "SELECT COUNT(*) FROM issued_books WHERE book_id = ? AND status = 'issued'");
mysqli_stmt_bind_param($check, 'i', $id);
mysqli_stmt_execute($check);
mysqli_stmt_bind_result($check, $activeIssues);
mysqli_stmt_fetch($check);
mysqli_stmt_close($check);

if ($activeIssues > 0) {
    flashMessage('danger', "Cannot delete \"{$book['title']}\" – it has $activeIssues active issued copy/copies.");
    redirect('books.php');
}

$del = mysqli_prepare($conn, "DELETE FROM books WHERE id = ?");
mysqli_stmt_bind_param($del, 'i', $id);

if (mysqli_stmt_execute($del)) {
    flashMessage('success', "Book \"{$book['title']}\" deleted successfully.");
} else {
    flashMessage('danger', 'Failed to delete book: ' . mysqli_error($conn));
}
mysqli_stmt_close($del);

redirect('books.php');
