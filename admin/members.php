<?php
require_once '../config.php';
requireLogin();

$activePage = 'members';
$search = sanitize($conn, $_GET['search'] ?? '');

if ($search !== '') {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM members WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY membership_date DESC"
    );
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM members ORDER BY membership_date DESC");
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Members</h5>
        <div class="topbar-right">
            <a href="add_member.php" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus me-1"></i>Add Member
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
                <h6 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>All Members</h6>
                <form method="GET" class="d-flex gap-2">
                    <div class="input-group" style="width:280px">
                        <input type="text" class="form-control form-control-sm" name="search"
                               placeholder="Search members…" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-sm btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($search): ?>
                            <a href="members.php" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Membership Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No members found.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="member-avatar">
                                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                        </div>
                                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['membership_date']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_member.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_member.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete">
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
