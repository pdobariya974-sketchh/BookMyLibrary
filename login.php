<?php
session_start();
// server-side login handler
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/db.php';

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, role FROM users WHERE email = ? AND password = ? LIMIT 1');
        $stmt->bind_param('ss', $email, $password);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            // redirect based on role
            if ($row['role'] === 'admin') {
                header('Location: admin/home.php');
                exit;
            } elseif ($row['role'] === 'librarian') {
                header('Location: librarian/home.php');
                exit;
            } else {
                header('Location: user/home.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Library System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="image/title_image.png" type="image/png">
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">
            <h1>Welcome Back</h1>
            <p class="subtitle">Login to your library account</p>

            <?php if ($error): ?>
                <div style="color:#ef4444;margin-bottom:12px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="">

                <!-- Email -->
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    <small class="error" id="emailError"></small>
                </div>

                <!-- Password -->
                <div class="form-group password-group">
                    <label>Password</label>

                    <div class="password-wrapper">
                        <input type="password" id="password" maxlength="6" name="password">

                        <i class="fa-solid fa-eye eye-icon" id="togglePassword"></i>
                    </div>

                    <small class="error" id="passwordError"></small>
                </div>
                <!-- Remember & Forgot -->
                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" id="rememberMe">
                        Remember me
                    </label>

                    <a href="forgot_password.php" class="forgot">Forgot password?</a>
                </div>

                <button type="submit">Login</button>

                <p class="back-to-login">
                    Don’t have an account?
                    <a href="register.php">Create one</a>
                </p>

            </form>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>

</html>