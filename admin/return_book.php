<?php
require_once '../config.php';
requireLogin();

$activePage = 'return';

// Handle return action
if (isset($_GET['return_id'])) {
    $returnId = (int)$_GET['return_id'];

    // Fetch the issued record
    $stmt = mysqli_prepare($conn,
        "SELECT ib.*, b.title FROM issued_books ib JOIN books b ON ib.book_id = b.id WHERE ib.id = ? AND ib.status = 'issued'"
    );
    mysqli_stmt_bind_param($stmt, 'i', $returnId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $record = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($record) {
        $today     = date('Y-m-d');
        $fine      = 0.00;
        $overdueDays = (int)max(0, (strtotime($today) - strtotime($record['return_date'])) / 86400);
        if ($overdueDays > 0) {
            $fine = $overdueDays * 1.00; // $1 per day fine
        }

        mysqli_begin_transaction($conn);
        try {
            $upd = mysqli_prepare($conn,
                "UPDATE issued_books SET actual_return_date=?, status='returned', fine=? WHERE id=?"
            );
            mysqli_stmt_bind_param($upd, 'sdi', $today, $fine, $returnId);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);

            $updBook = mysqli_prepare($conn, "UPDATE books SET available = available + 1 WHERE id = ?");
            mysqli_stmt_bind_param($updBook, 'i', $record['book_id']);
            mysqli_stmt_execute($updBook);
            mysqli_stmt_close($updBook);

            mysqli_commit($conn);

            $msg = "Book \"{$record['title']}\" returned successfully.";
            if ($fine > 0) {
                $msg .= " Fine collected: \$$fine (overdue by $overdueDays day(s)).";
            }
            flashMessage('success', $msg);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            flashMessage('danger', 'Return failed: ' . $e->getMessage());
        }
    } else {
        flashMessage('danger', 'Record not found or already returned.');
    }

    redirect('return_book.php');
}

// Fetch all issued (not yet returned) books
$issuedResult = mysqli_query($conn,
    "SELECT ib.id, b.title, b.author, m.name AS member_name, m.phone,
            ib.issue_date, ib.return_date,
            DATEDIFF(CURDATE(), ib.return_date) AS overdue_days
     FROM issued_books ib
     JOIN books b ON ib.book_id = b.id
     JOIN members m ON ib.member_id = m.id
     WHERE ib.status = 'issued'
     ORDER BY ib.return_date ASC"
);

// Fetch recently returned books (last 10)
$returnedResult = mysqli_query($conn,
    "SELECT ib.id, b.title, m.name AS member_name,
            ib.issue_date, ib.return_date, ib.actual_return_date, ib.fine
     FROM issued_books ib
     JOIN books b ON ib.book_id = b.id
     JOIN members m ON ib.member_id = m.id
     WHERE ib.status = 'returned'
     ORDER BY ib.actual_return_date DESC LIMIT 10"
);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Return Book</h5>
    </div>

    <div class="content-area">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Currently Issued (pending return) -->
        <div class="card card-shadow mb-4">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="fas fa-clock me-2 text-warning"></i>Pending Returns</h6>
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
                                <th>Overdue</th>
                                <th>Est. Fine</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($issuedResult) === 0): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No pending returns.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; while ($row = mysqli_fetch_assoc($issuedResult)): ?>
                            <?php $overdue = (int)$row['overdue_days']; $fine = max(0, $overdue) * 1.00; ?>
                            <tr class="<?= $overdue > 0 ? 'table-danger-light' : '' ?>">
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['author']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['member_name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['phone']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['issue_date']) ?></td>
                                <td><?= htmlspecialchars($row['return_date']) ?></td>
                                <td>
                                    <?php if ($overdue > 0): ?>
                                        <span class="badge bg-danger"><?= $overdue ?> day(s)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">On time</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($fine > 0): ?>
                                        <span class="text-danger fw-bold">$<?= number_format($fine, 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="return_book.php?return_id=<?= (int)$row['id'] ?>"
                                       class="btn btn-sm btn-success confirm-return"
                                       data-title="<?= htmlspecialchars($row['title']) ?>"
                                       data-fine="<?= $fine ?>">
                                        <i class="fas fa-undo-alt me-1"></i>Return
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

        <!-- Recently Returned -->
        <div class="card card-shadow">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="fas fa-check-double me-2 text-success"></i>Recently Returned (Last 10)</h6>
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
                                <th>Returned On</th>
                                <th>Fine Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($returnedResult) === 0): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No returned books yet.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; while ($row = mysqli_fetch_assoc($returnedResult)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['member_name']) ?></td>
                                <td><?= htmlspecialchars($row['issue_date']) ?></td>
                                <td><?= htmlspecialchars($row['return_date']) ?></td>
                                <td><?= htmlspecialchars($row['actual_return_date']) ?></td>
                                <td>
                                    <?php if ($row['fine'] > 0): ?>
                                        <span class="text-danger fw-bold">$<?= number_format($row['fine'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
