<?php
require_once '../config.php';
requireLogin();

$activePage = 'members';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    flashMessage('danger', 'Invalid member ID.');
    redirect('members.php');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM members WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$member) {
    flashMessage('danger', 'Member not found.');
    redirect('members.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = trim($_POST['name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $phone           = trim($_POST['phone'] ?? '');
    $address         = trim($_POST['address'] ?? '');
    $membership_date = trim($_POST['membership_date'] ?? date('Y-m-d'));
    $status          = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

    if (!$name)  $errors[] = 'Name is required.';
    if (!$email) $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

    if (empty($errors)) {
        $upd = mysqli_prepare($conn,
            "UPDATE members SET name=?, email=?, phone=?, address=?, membership_date=?, status=? WHERE id=?"
        );
        mysqli_stmt_bind_param($upd, 'ssssssi', $name, $email, $phone, $address, $membership_date, $status, $id);

        if (mysqli_stmt_execute($upd)) {
            flashMessage('success', "Member \"$name\" updated successfully.");
            redirect('members.php');
        } else {
            if (mysqli_errno($conn) === 1062) {
                $errors[] = 'A member with this email already exists.';
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
            }
        }
        mysqli_stmt_close($upd);
    }

    $member['name']            = $_POST['name'];
    $member['email']           = $_POST['email'];
    $member['phone']           = $_POST['phone'];
    $member['address']         = $_POST['address'];
    $member['membership_date'] = $_POST['membership_date'];
    $member['status']          = $_POST['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Edit Member</h5>
        <div class="topbar-right">
            <a href="members.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="content-area">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-shadow">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="fas fa-user-edit me-2 text-warning"></i>Edit Member Details</h6>
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
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name"
                                           value="<?= htmlspecialchars($member['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?= htmlspecialchars($member['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" class="form-control" name="phone"
                                           value="<?= htmlspecialchars($member['phone']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Membership Date</label>
                                    <input type="date" class="form-control" name="membership_date"
                                           value="<?= htmlspecialchars($member['membership_date']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="active"   <?= $member['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($member['address']) ?></textarea>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-1"></i>Update Member
                                    </button>
                                    <a href="members.php" class="btn btn-outline-secondary">Cancel</a>
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
