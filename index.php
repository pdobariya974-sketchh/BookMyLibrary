<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = md5($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = mysqli_prepare($conn, "SELECT id, username, email FROM admin WHERE username = ? AND password = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['admin_id']   = $row['id'];
            $_SESSION['admin_user'] = $row['username'];
            $_SESSION['admin_email']= $row['email'];
            redirect('admin/dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookMyLibrary – Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }
        .login-logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .login-logo i { color: #fff; font-size: 2rem; }
        .login-title { font-weight: 700; color: #2c3e50; text-align: center; }
        .login-subtitle { color: #7f8c8d; text-align: center; font-size: .9rem; margin-bottom: 1.5rem; }
        .form-control:focus { border-color: #3498db; box-shadow: 0 0 0 .2rem rgba(52,152,219,.25); }
        .btn-login {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            border: none;
            border-radius: 8px;
            padding: .75rem;
            font-weight: 600;
            letter-spacing: .5px;
            width: 100%;
            color: #fff;
            transition: opacity .2s;
        }
        .btn-login:hover { opacity: .9; color: #fff; }
        .input-group-text { background: #f0f4f8; border-right: none; }
        .form-control { border-left: none; }
        .alert { border-radius: 8px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-book-open"></i>
        </div>
        <h4 class="login-title">BookMyLibrary</h4>
        <p class="login-subtitle">Library Management System</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control" name="username"
                           placeholder="Enter username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control" name="password"
                           placeholder="Enter password" required id="passwordInput">
                    <button type="button" class="input-group-text border-start-0 bg-white"
                            onclick="togglePassword()">
                        <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>
        <p class="text-center text-muted mt-3 mb-0" style="font-size:.8rem;">
            Default: <strong>admin</strong> / <strong>admin123</strong>
        </p>
    </div>

    <script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>
