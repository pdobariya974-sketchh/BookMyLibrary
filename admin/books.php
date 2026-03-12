<?php
require_once '../config.php';
requireLogin();

$activePage = 'books';
$search = sanitize($conn, $_GET['search'] ?? '');

if ($search !== '') {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? OR category LIKE ? ORDER BY added_date DESC"
    );
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM books ORDER BY added_date DESC");
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Books</h5>
        <div class="topbar-right">
            <a href="add_book.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add Book
            </a>
        </div>
    </div>

    <div class="content-area">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card card-shadow">
            <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0"><i class="fas fa-book me-2 text-primary"></i>All Books</h6>
                <form method="GET" class="d-flex gap-2">
                    <div class="input-group" style="width:280px">
                        <input type="text" class="form-control form-control-sm" name="search"
                               placeholder="Search books…" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-sm btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($search): ?>
                            <a href="books.php" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="booksTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No books found.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                <td><?= htmlspecialchars($row['author']) ?></td>
                                <td><code><?= htmlspecialchars($row['isbn']) ?></code></td>
                                <td><span class="badge-category"><?= htmlspecialchars($row['category']) ?></span></td>
                                <td><?= (int)$row['quantity'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['available'] > 0 ? 'success' : 'danger' ?>">
                                        <?= (int)$row['available'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_book.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_book.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
