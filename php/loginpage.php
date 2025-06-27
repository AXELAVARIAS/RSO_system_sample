<?php
session_start();
// Simple login and registration handler (demo only)
$login_error = '';
$register_error = '';
$register_success = '';

// Hardcoded admin credentials
$admin_email = 'admin';
$admin_password = 'admin';
$admin_user_type = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic
        $reg_email = $_POST['reg_email'] ?? '';
        $reg_password = $_POST['reg_password'] ?? '';
        $reg_userType = $_POST['reg_user_type'] ?? '';
        $reg_fullName = $_POST['reg_full_name'] ?? '';
        $reg_department = $_POST['reg_department'] ?? '';
        
        if ($reg_userType === 'admin') {
            $register_error = 'Admin account cannot be registered.';
        } elseif ($reg_email && $reg_password && $reg_userType && $reg_fullName && $reg_department) {
            $users_file = 'users.csv';
            $user_exists = false;
            if (file_exists($users_file)) {
                $file = fopen($users_file, 'r');
                while (($data = fgetcsv($file)) !== false) {
                    if ($data[0] === $reg_email) {
                        $user_exists = true;
                        break;
                    }
                }
                fclose($file);
            }
            if ($user_exists) {
                $register_error = 'User already exists.';
            } else {
                $file = fopen($users_file, 'a');
                fputcsv($file, [$reg_email, password_hash($reg_password, PASSWORD_DEFAULT), $reg_userType, $reg_fullName, $reg_department]);
                fclose($file);
                // Auto-login after registration
                $_SESSION['logged_in'] = true;
                $_SESSION['user_email'] = $reg_email;
                $_SESSION['user_type'] = $reg_userType;
                $_SESSION['user_full_name'] = $reg_fullName;
                $_SESSION['user_department'] = $reg_department;
                header('Location: ../index.php');
                exit;
            }
        } else {
            $register_error = 'Please fill in all fields.';
        }
    } else {
        // Login logic
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $users_file = 'users.csv';
        $login_valid = false;
        $is_admin = false;
        $user_full_name = '';
        $user_department = '';
        $user_type = '';
        
        // Admin login: check first
        if ($email === $admin_email && $password === $admin_password) {
            $login_valid = true;
            $is_admin = true;
        } elseif ($email && $password && file_exists($users_file)) {
            $file = fopen($users_file, 'r');
            while (($data = fgetcsv($file)) !== false) {
                if ($data[0] === $email && password_verify($password, $data[1])) {
                    $login_valid = true;
                    $user_type = $data[2] ?? '';
                    $user_full_name = $data[3] ?? '';
                    $user_department = $data[4] ?? '';
                    break;
                }
            }
            fclose($file);
        }
        if ($login_valid) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $is_admin ? 'admin' : $user_type;
            $_SESSION['user_full_name'] = $user_full_name;
            $_SESSION['user_department'] = $user_department;
            if ($is_admin) {
                $_SESSION['admin_logged_in'] = true;
                header('Location: manage_faculty.php');
            
            } else {
                header('Location: ../index.php');
            }
            exit;
        } else {
            $login_error = 'Invalid credentials.';
        }
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
   
    <div class="login-container">
        <div class="logo">
            <img src="../pics/rso-bg.png" alt="UC Logo">
            <h1>RSO Research Management System</h1>
        </div>
        <div id="login-section" style="display:<?php echo ($register_success || isset($_POST['register'])) ? 'none' : 'block'; ?>;">
            <?php if ($login_error): ?>
            <div class="login-error" style="color:red; margin-bottom:10px; text-align:center;"> <?php echo htmlspecialchars($login_error); ?> </div>
            <?php endif; ?>
            <?php if ($register_success): ?>
            <div class="login-success" style="color:green; margin-bottom:10px; text-align:center;"> <?php echo htmlspecialchars($register_success); ?> </div>
            <?php endif; ?>
            <form id="loginForm" method="post" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" required placeholder="Enter your email or username" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
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
            <div style="text-align:center; margin-top:10px;">
                <a href="#" onclick="showRegister(); return false;">Create Account</a>
            </div>
        </div>
        <div id="register-section" style="display:<?php echo ($register_success || isset($_POST['register'])) ? 'block' : 'none'; ?>;">
            <div class="user-type-selector">
                <button class="user-type-btn<?php if (empty($_POST['reg_user_type']) || $_POST['reg_user_type'] === 'faculty') echo ' active'; ?>" type="button" onclick="selectRegUserType(this, 'faculty')">Faculty Member</button>
                <button class="user-type-btn<?php if (!empty($_POST['reg_user_type']) && $_POST['reg_user_type'] === 'rso') echo ' active'; ?>" type="button" onclick="selectRegUserType(this, 'rso')">RSO Member</button>
            </div>
            <?php if ($register_error): ?>
            <div class="login-error" style="color:red; margin-bottom:10px; text-align:center;"> <?php echo htmlspecialchars($register_error); ?> </div>
            <?php endif; ?>
            <form id="registerForm" method="post" action="">
                <input type="hidden" name="reg_user_type" id="reg_user_type" value="<?php echo !empty($_POST['reg_user_type']) ? htmlspecialchars($_POST['reg_user_type']) : 'faculty'; ?>">
                <div class="form-group">
                    <label for="reg_full_name">Full Name</label>
                    <input type="text" id="reg_full_name" name="reg_full_name" required placeholder="Enter your full name" value="<?php echo isset($_POST['reg_full_name']) ? htmlspecialchars($_POST['reg_full_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="reg_department">Department</label>
                    <input type="text" id="reg_department" name="reg_department" required placeholder="Enter your department" value="<?php echo isset($_POST['reg_department']) ? htmlspecialchars($_POST['reg_department']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="reg_email" required placeholder="Enter your email" value="<?php echo isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="reg_password" required placeholder="Enter your password">
                </div>
                <button type="submit" name="register" class="login-btn">Create Account</button>
            </form>
            <div style="text-align:center; margin-top:10px;">
                <a href="#" onclick="showLogin(); return false;">Back to Login</a>
            </div>
        </div>
    </div>
    <script>
        function selectRegUserType(button, type) {
            document.querySelectorAll('#register-section .user-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
            document.getElementById('reg_user_type').value = type;
        }
        function showRegister() {
            document.getElementById('login-section').style.display = 'none';
            document.getElementById('register-section').style.display = 'block';
        }
        function showLogin() {
            document.getElementById('register-section').style.display = 'none';
            document.getElementById('login-section').style.display = 'block';
        }
    </script>
</body>
</html> 