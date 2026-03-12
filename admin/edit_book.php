<?php
require_once '../config.php';
requireLogin();

$activePage = 'books';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    flashMessage('danger', 'Invalid book ID.');
    redirect('books.php');
}

// Load existing book
$stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$book) {
    flashMessage('danger', 'Book not found.');
    redirect('books.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $isbn        = trim($_POST['isbn'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $quantity    = (int)($_POST['quantity'] ?? 1);
    $description = trim($_POST['description'] ?? '');

    if (!$title)    $errors[] = 'Title is required.';
    if (!$author)   $errors[] = 'Author is required.';
    if (!$category) $errors[] = 'Category is required.';
    if ($quantity < 1) $errors[] = 'Quantity must be at least 1.';

    // Ensure new quantity is not less than currently issued copies
    $issued = $book['quantity'] - $book['available'];
    if (empty($errors) && $quantity < $issued) {
        $errors[] = "Quantity cannot be less than the number of currently issued copies ($issued).";
    }

    if (empty($errors)) {
        $newAvailable = max(0, $quantity - $issued);

        $upd = mysqli_prepare($conn,
            "UPDATE books SET title=?, author=?, isbn=?, category=?, quantity=?, available=?, description=? WHERE id=?"
        );
        mysqli_stmt_bind_param($upd, 'ssssiisi', $title, $author, $isbn, $category, $quantity, $newAvailable, $description, $id);

        if (mysqli_stmt_execute($upd)) {
            flashMessage('success', "Book \"$title\" updated successfully.");
            redirect('books.php');
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($upd);
    }

    // On error, use POST values for re-filling the form
    $book['title']       = $_POST['title'];
    $book['author']      = $_POST['author'];
    $book['isbn']        = $_POST['isbn'];
    $book['category']    = $_POST['category'];
    $book['quantity']    = $_POST['quantity'];
    $book['description'] = $_POST['description'];
}

$categories = ['Fiction','Non-Fiction','Science','Technology','History','Biography','Self-Help','Fantasy','Mystery','Romance','Psychology','Dystopian','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Edit Book</h5>
        <div class="topbar-right">
            <a href="books.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="content-area">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-shadow">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="fas fa-edit me-2 text-warning"></i>Edit Book Details</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" novalidate>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title"
                                           value="<?= htmlspecialchars($book['title']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">ISBN</label>
                                    <input type="text" class="form-control" name="isbn"
                                           value="<?= htmlspecialchars($book['isbn']) ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Author <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="author"
                                           value="<?= htmlspecialchars($book['author']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="quantity" min="1"
                                           value="<?= htmlspecialchars($book['quantity']) ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category" required>
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat ?>" <?= ($book['category'] === $cat) ? 'selected' : '' ?>>
                                                <?= $cat ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($book['description']) ?></textarea>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-1"></i>Update Book
                                    </button>
                                    <a href="books.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
