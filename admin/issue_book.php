<?php
require_once '../config.php';
requireLogin();

$activePage = 'issue';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id   = (int)($_POST['member_id'] ?? 0);
    $book_id     = (int)($_POST['book_id'] ?? 0);
    $issue_date  = trim($_POST['issue_date'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');

    if (!$member_id)   $errors[] = 'Please select a member.';
    if (!$book_id)     $errors[] = 'Please select a book.';
    if (!$issue_date)  $errors[] = 'Issue date is required.';
    if (!$return_date) $errors[] = 'Expected return date is required.';
    if ($issue_date && $return_date && $return_date <= $issue_date) {
        $errors[] = 'Return date must be after issue date.';
    }

    if (empty($errors)) {
        // Check member exists and is active
        $mCheck = mysqli_prepare($conn, "SELECT id FROM members WHERE id = ? AND status = 'active'");
        mysqli_stmt_bind_param($mCheck, 'i', $member_id);
        mysqli_stmt_execute($mCheck);
        mysqli_stmt_store_result($mCheck);
        if (mysqli_stmt_num_rows($mCheck) === 0) {
            $errors[] = 'Selected member is inactive or not found.';
        }
        mysqli_stmt_close($mCheck);

        // Check book availability
        $bCheck = mysqli_prepare($conn, "SELECT available, title FROM books WHERE id = ? AND available > 0");
        mysqli_stmt_bind_param($bCheck, 'i', $book_id);
        mysqli_stmt_execute($bCheck);
        $bResult = mysqli_stmt_get_result($bCheck);
        $bookRow = mysqli_fetch_assoc($bResult);
        mysqli_stmt_close($bCheck);

        if (!$bookRow) {
            $errors[] = 'Selected book is not available.';
        }
    }

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            $ins = mysqli_prepare($conn,
                "INSERT INTO issued_books (book_id, member_id, issue_date, return_date, status) VALUES (?, ?, ?, ?, 'issued')"
            );
            mysqli_stmt_bind_param($ins, 'iiss', $book_id, $member_id, $issue_date, $return_date);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);

            $upd = mysqli_prepare($conn, "UPDATE books SET available = available - 1 WHERE id = ? AND available > 0");
            mysqli_stmt_bind_param($upd, 'i', $book_id);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);

            mysqli_commit($conn);
            flashMessage('success', "Book issued successfully.");
            redirect('issue_book.php');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = 'Transaction failed: ' . $e->getMessage();
        }
    }
}

// Data for dropdowns
$members = mysqli_query($conn, "SELECT id, name, email FROM members WHERE status='active' ORDER BY name");
$books   = mysqli_query($conn, "SELECT id, title, author, available FROM books WHERE available > 0 ORDER BY title");

// Currently issued books
$issued = mysqli_query($conn,
    "SELECT ib.id, b.title, b.author, m.name AS member_name, ib.issue_date, ib.return_date,
            DATEDIFF(CURDATE(), ib.return_date) AS overdue_days
     FROM issued_books ib
     JOIN books b ON ib.book_id = b.id
     JOIN members m ON ib.member_id = m.id
     WHERE ib.status = 'issued'
     ORDER BY ib.issue_date DESC"
);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Issue Book</h5>
    </div>

    <div class="content-area">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Issue Form -->
            <div class="col-lg-4">
                <div class="card card-shadow">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="fas fa-hand-holding-heart me-2 text-primary"></i>Issue a Book</h6>
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
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Member <span class="text-danger">*</span></label>
                                <select class="form-select" name="member_id" required>
                                    <option value="">-- Select Member --</option>
                                    <?php while ($m = mysqli_fetch_assoc($members)): ?>
                                        <option value="<?= $m['id'] ?>"
                                            <?= (($_POST['member_id'] ?? '') == $m['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['email']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Book <span class="text-danger">*</span></label>
                                <select class="form-select" name="book_id" required>
                                    <option value="">-- Select Book --</option>
                                    <?php while ($b = mysqli_fetch_assoc($books)): ?>
                                        <option value="<?= $b['id'] ?>"
                                            <?= (($_POST['book_id'] ?? '') == $b['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($b['title']) ?> (<?= (int)$b['available'] ?> left)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="issue_date"
                                       value="<?= htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Expected Return Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="return_date"
                                       value="<?= htmlspecialchars($_POST['return_date'] ?? date('Y-m-d', strtotime('+14 days'))) ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-check me-1"></i>Issue Book
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Currently Issued Books -->
            <div class="col-lg-8">
                <div class="card card-shadow">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Currently Issued Books</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Book</th>
                                        <th>Member</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (mysqli_num_rows($issued) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No books currently issued.</td></tr>
                                <?php else: ?>
                                    <?php $i = 1; while ($row = mysqli_fetch_assoc($issued)): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($row['author']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['member_name']) ?></td>
                                        <td><?= htmlspecialchars($row['issue_date']) ?></td>
                                        <td><?= htmlspecialchars($row['return_date']) ?></td>
                                        <td>
                                            <?php if ($row['overdue_days'] > 0): ?>
                                                <span class="badge bg-danger">Overdue (<?= $row['overdue_days'] ?>d)</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Issued</span>
                                            <?php endif; ?>
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
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
