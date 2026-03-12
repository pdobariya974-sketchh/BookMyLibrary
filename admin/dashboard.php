<?php
require_once '../config.php';
requireLogin();

$activePage = 'dashboard';

// Stats
$totalBooks   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM books"))[0];
$totalMembers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM members"))[0];
$issuedBooks  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM issued_books WHERE status='issued'"))[0];
$availableBooks = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(available) FROM books"))[0] ?? 0;

// Category distribution for chart
$catResult = mysqli_query($conn, "SELECT category, COUNT(*) as cnt FROM books GROUP BY category ORDER BY cnt DESC");
$categories = [];
$catCounts  = [];
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row['category'];
    $catCounts[]  = (int)$row['cnt'];
}

// Recent issued books
$recentResult = mysqli_query($conn,
    "SELECT ib.id, b.title, m.name AS member_name, ib.issue_date, ib.return_date, ib.status
     FROM issued_books ib
     JOIN books b ON ib.book_id = b.id
     JOIN members m ON ib.member_id = m.id
     ORDER BY ib.issue_date DESC LIMIT 10"
);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Dashboard</h5>
        <div class="topbar-right">
            <span class="text-muted small"><i class="fas fa-calendar-alt me-1"></i><?= date('D, d M Y') ?></span>
        </div>
    </div>

    <div class="content-area">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card stat-blue">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $totalBooks ?></div>
                        <div class="stat-label">Total Books</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card stat-green">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $totalMembers ?></div>
                        <div class="stat-label">Total Members</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card stat-orange">
                    <div class="stat-icon"><i class="fas fa-hand-holding-heart"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $issuedBooks ?></div>
                        <div class="stat-label">Books Issued</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card stat-teal">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $availableBooks ?></div>
                        <div class="stat-label">Books Available</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card card-shadow">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Books by Category</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card card-shadow">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Issue Status</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <canvas id="statusChart" height="220" style="max-width:260px"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Issued Books -->
        <div class="card card-shadow">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Recent Issued Books</h6>
                <a href="issue_book.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Book Title</th>
                                <th>Member</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($recentResult) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No records found.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; while ($row = mysqli_fetch_assoc($recentResult)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['member_name']) ?></td>
                                <td><?= htmlspecialchars($row['issue_date']) ?></td>
                                <td><?= htmlspecialchars($row['return_date']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($row['status']) {
                                        'issued'   => 'warning',
                                        'returned' => 'success',
                                        'overdue'  => 'danger',
                                        default    => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?> text-capitalize">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- /content-area -->
</div><!-- /main-content -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="../js/main.js"></script>
<script>
const categoryLabels = <?= json_encode($categories) ?>;
const categoryCounts = <?= json_encode($catCounts) ?>;
const issuedCount    = <?= (int)$issuedBooks ?>;
const returnedCount  = <?= (int)mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM issued_books WHERE status='returned'"))[0] ?>;
const overdueCount   = <?= (int)mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM issued_books WHERE status='overdue'"))[0] ?>;

initCategoryChart('categoryChart', categoryLabels, categoryCounts);
initStatusChart('statusChart', issuedCount, returnedCount, overdueCount);
</script>
</body>
</html>
