<?php
require_once '../config.php';
requireLogin();

$activePage = 'members';
$errors = [];

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
        $stmt = mysqli_prepare($conn,
            "INSERT INTO members (name, email, phone, address, membership_date, status) VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $phone, $address, $membership_date, $status);

        if (mysqli_stmt_execute($stmt)) {
            flashMessage('success', "Member \"$name\" added successfully.");
            redirect('members.php');
        } else {
            if (mysqli_errno($conn) === 1062) {
                $errors[] = 'A member with this email already exists.';
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member – BookMyLibrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h5 class="mb-0">Add Member</h5>
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
                        <h6 class="mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>New Member Details</h6>
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
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                           placeholder="Member's full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                           placeholder="email@example.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" class="form-control" name="phone"
                                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                           placeholder="Phone number">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Membership Date</label>
                                    <input type="date" class="form-control" name="membership_date"
                                           value="<?= htmlspecialchars($_POST['membership_date'] ?? date('Y-m-d')) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="active"   <?= (($_POST['status'] ?? 'active') === 'active')   ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= (($_POST['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea class="form-control" name="address" rows="3"
                                              placeholder="Full address…"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Add Member
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
