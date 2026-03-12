<?php
// Sidebar partial – included by every admin page.
// $activePage must be set before including this file.
$activePage = $activePage ?? '';
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-book-open me-2"></i>
        <span>BookMyLibrary</span>
    </div>
    <div class="sidebar-admin">
        <div class="admin-avatar">
            <i class="fas fa-user-circle fa-2x"></i>
        </div>
        <div>
            <div class="admin-name"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></div>
            <div class="admin-role">Administrator</div>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-section">MAIN</li>
        <li class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        </li>
        <li class="nav-section">LIBRARY</li>
        <li class="<?= $activePage === 'books' ? 'active' : '' ?>">
            <a href="books.php"><i class="fas fa-book"></i>Books</a>
        </li>
        <li class="<?= $activePage === 'members' ? 'active' : '' ?>">
            <a href="members.php"><i class="fas fa-users"></i>Members</a>
        </li>
        <li class="nav-section">TRANSACTIONS</li>
        <li class="<?= $activePage === 'issue' ? 'active' : '' ?>">
            <a href="issue_book.php"><i class="fas fa-hand-holding-heart"></i>Issue Book</a>
        </li>
        <li class="<?= $activePage === 'return' ? 'active' : '' ?>">
            <a href="return_book.php"><i class="fas fa-undo-alt"></i>Return Book</a>
        </li>
        <li class="nav-section">ACCOUNT</li>
        <li>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </li>
    </ul>
</nav>
