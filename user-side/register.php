<?php
session_start();
include_once './includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($fullname === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if email already exists
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($res) > 0) {
            $error = 'An account with this email address already exists.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer'; // Default role
            
            // Insert user
            $stmt_ins = mysqli_prepare($conn, "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_ins, "ssss", $fullname, $email, $hashed_password, $role);
            if (mysqli_stmt_execute($stmt_ins)) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Plantea</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include './includes/navbar.php'; ?>

<section class="auth-container">
    <div class="auth-box">
        <h2>Create an Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="color: #c9302c; background-color: #f2dede; border: 1px solid #ebccd1; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="color: #449d44; background-color: #dff0d8; border: 1px solid #d6e9c6; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="input-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
            </div>
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="Create a password" required style="padding-right: 45px;">
                    <button type="button" id="toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #888; font-size: 16px; padding: 0;">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div id="strength-meter" style="margin-top: 8px;">
                    <div style="display: flex; gap: 4px; margin-bottom: 4px;">
                        <div id="str-bar-1" style="height: 4px; flex: 1; border-radius: 2px; background: #e0e0e0; transition: background 0.3s;"></div>
                        <div id="str-bar-2" style="height: 4px; flex: 1; border-radius: 2px; background: #e0e0e0; transition: background 0.3s;"></div>
                        <div id="str-bar-3" style="height: 4px; flex: 1; border-radius: 2px; background: #e0e0e0; transition: background 0.3s;"></div>
                    </div>
                    <span id="str-label" style="font-size: 12px; color: #999;"></span>
                </div>
            </div>
            <button type="submit" class="auth-btn">Register</button>
        </form>
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</section>

<?php include './includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/Hide password toggle
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

    // Password strength meter
    var bar1 = document.getElementById('str-bar-1');
    var bar2 = document.getElementById('str-bar-2');
    var bar3 = document.getElementById('str-bar-3');
    var label = document.getElementById('str-label');

    passwordInput.addEventListener('input', function() {
        var val = this.value;
        var score = 0;

        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        // Reset
        var defaultColor = '#e0e0e0';
        bar1.style.background = defaultColor;
        bar2.style.background = defaultColor;
        bar3.style.background = defaultColor;
        label.textContent = '';
        label.style.color = '#999';

        if (val.length === 0) return;

        if (score <= 2) {
            bar1.style.background = '#e63946';
            label.textContent = 'Weak';
            label.style.color = '#e63946';
        } else if (score <= 3) {
            bar1.style.background = '#f4a261';
            bar2.style.background = '#f4a261';
            label.textContent = 'Medium';
            label.style.color = '#f4a261';
        } else {
            bar1.style.background = '#2a9d5c';
            bar2.style.background = '#2a9d5c';
            bar3.style.background = '#2a9d5c';
            label.textContent = 'Strong';
            label.style.color = '#2a9d5c';
        }
    });
});
</script>

</body>
</html>
