<?php
// Simple login handler (demo only)
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $userType = $_POST['user_type'] ?? '';
    // Demo: accept any credentials
    if ($email && $password && $userType) {
        // In real use, check credentials here
        header('Location: ../index.php');
        exit;
    } else {
        $login_error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login page Research Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <a href="../index.php" class="back-btn">Back to Dashboard</a>
    <div class="login-container">
        <div class="logo">
            <img src="../pics/rso-bg.png" alt="UC Logo">
            <h1>RSO Research Management System</h1>
        </div>
        <div class="user-type-selector">
            <button class="user-type-btn<?php if (empty($_POST['user_type']) || $_POST['user_type'] === 'faculty') echo ' active'; ?>" type="button" onclick="selectUserType(this, 'faculty')">Faculty Member</button>
            <button class="user-type-btn<?php if (!empty($_POST['user_type']) && $_POST['user_type'] === 'rso') echo ' active'; ?>" type="button" onclick="selectUserType(this, 'rso')">RSO Member</button>
        </div>
        <?php if ($login_error): ?>
        <div class="login-error" style="color:red; margin-bottom:10px; text-align:center;"> <?php echo htmlspecialchars($login_error); ?> </div>
        <?php endif; ?>
        <form id="loginForm" method="post" action="">
            <input type="hidden" name="user_type" id="user_type" value="<?php echo !empty($_POST['user_type']) ? htmlspecialchars($_POST['user_type']) : 'faculty'; ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>
    </div>
    <script>
        function selectUserType(button, type) {
            document.querySelectorAll('.user-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
            document.getElementById('user_type').value = type;
        }
    </script>
</body>
</html> 