<?php
session_start();
include_once './includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT user_id, username, password_hash, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($res) === 1) {
            $user = mysqli_fetch_assoc($res);
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Plantea</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include './includes/navbar.php'; ?>

<section class="auth-container">
    <div class="auth-box">
        <h2>Welcome Back</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="color: #c9302c; background-color: #f2dede; border: 1px solid #ebccd1; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required style="padding-right: 45px;">
                    <button type="button" id="toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #888; font-size: 16px; padding: 0;">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="auth-btn">Login</button>
        </form>
        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</section>

<?php include './includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('toggle-password');
    var passwordInput = document.getElementById('password');
    var icon = toggleBtn.querySelector('i');

    toggleBtn.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});
</script>

</body>
</html>
