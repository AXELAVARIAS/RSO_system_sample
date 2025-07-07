<?php
session_start();

// Include database configuration
require_once '../database/config.php';

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
            try {
                $db = getDB();
                
                // Check if user already exists
                $existing_user = $db->fetch("SELECT id FROM users WHERE email = ?", [$reg_email]);
                
                if ($existing_user) {
                    $register_error = 'User already exists.';
                } else {
                    // Insert new user
                    $db->query("INSERT INTO users (email, password_hash, user_type, full_name, department) VALUES (?, ?, ?, ?, ?)", 
                        [$reg_email, password_hash($reg_password, PASSWORD_DEFAULT), $reg_userType, $reg_fullName, $reg_department]);
                    
                    // Auto-login after registration
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_email'] = $reg_email;
                    $_SESSION['user_type'] = $reg_userType;
                    $_SESSION['user_full_name'] = $reg_fullName;
                    $_SESSION['user_department'] = $reg_department;
                    $_SESSION['profile_picture'] = '../pics/rso-bg.png';
                    
                    header('Location: ../index.php');
                    exit;
                }
            } catch (Exception $e) {
                $register_error = 'Registration failed. Please try again.';
            }
        } else {
            $register_error = 'Please fill in all fields.';
        }
    } else {
        // Login logic
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $login_valid = false;
        $is_admin = false;
        $user_data = null;
        
        // Admin login: check first
        if ($email === $admin_email && $password === $admin_password) {
            $login_valid = true;
            $is_admin = true;
        } elseif ($email && $password) {
            try {
                $db = getDB();
                
                // Get user from database
                $user_data = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
                
                if ($user_data && password_verify($password, $user_data['password_hash'])) {
                    $login_valid = true;
                }
            } catch (Exception $e) {
                $login_error = 'Database connection error.';
            }
        }
        
        if ($login_valid) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_email'] = $email;
            
            if ($is_admin) {
                $_SESSION['user_type'] = 'admin';
                $_SESSION['user_full_name'] = 'Administrator';
                $_SESSION['user_department'] = 'System Admin';
                $_SESSION['profile_picture'] = '../pics/rso-bg.png';
                $_SESSION['admin_logged_in'] = true;
                header('Location: manage_faculty.php');
            } else {
                $_SESSION['user_type'] = $user_data['user_type'];
                $_SESSION['user_full_name'] = $user_data['full_name'];
                $_SESSION['user_department'] = $user_data['department'];
                $_SESSION['profile_picture'] = $user_data['profile_picture'] ?? '../pics/rso-bg.png';
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
    <title>Login - RSO Research Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/modern-theme.css">
    <link rel="stylesheet" href="../css/theme.css">
</head>
<body>
    <!-- Theme Toggle for Login Page -->
    <button class="theme-toggle" title="Toggle Theme" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="../pics/rso-bg.png" alt="UC Logo">
                <h1>RSO Research Management System</h1>
            </div>
            
            <div class="login-tabs">
                <button class="login-tab active" id="loginTab">Login</button>
                <button class="login-tab" id="registerTab">Register</button>
            </div>
            
            <!-- Login Form -->
            <div class="login-form active" id="loginForm">
                <?php if ($login_error): ?>
                    <div class="form-error"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                <?php if ($register_success): ?>
                    <div class="form-success"><?php echo htmlspecialchars($register_success); ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="email">Email or Username</label>
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
            </div>
            
            <!-- Register Form -->
            <div class="login-form" id="registerForm">
                <?php if ($register_error): ?>
                    <div class="form-error"><?php echo htmlspecialchars($register_error); ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <input type="hidden" name="reg_user_type" id="reg_user_type" value="faculty">
                    
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
                    
                    <div class="form-group">
                        <label>User Type</label>
                        <div class="user-type-selector">
                            <button type="button" class="user-type-btn active" data-type="faculty">
                                <i class="fas fa-user-graduate"></i>
                                Faculty Member
                            </button>
                            <button type="button" class="user-type-btn" data-type="rso">
                                <i class="fas fa-users"></i>
                                RSO Member
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" name="register" class="login-btn">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .user-type-selector {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }
        
        .user-type-btn {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px 12px;
            border: 2px solid var(--border-primary);
            border-radius: 8px;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .user-type-btn:hover {
            border-color: var(--btn-primary-bg);
            color: var(--btn-primary-bg);
        }
        
        .user-type-btn.active {
            border-color: var(--btn-primary-bg);
            background: var(--bg-tertiary);
            color: var(--btn-primary-bg);
        }
        
        .user-type-btn i {
            font-size: 1.25rem;
        }
        
        .login-tabs {
            display: flex;
            margin-bottom: 32px;
            border-bottom: 1px solid var(--border-primary);
        }
        
        .login-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 2px solid transparent;
            font-size: 1rem;
        }
        
        .login-tab.active {
            color: var(--btn-primary-bg);
            border-bottom-color: var(--btn-primary-bg);
        }
        
        .login-form {
            display: none;
        }
        
        .login-form.active {
            display: block;
        }
    </style>

    <script src="../js/theme.js"></script>
    <script>
        // Tab switching
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const regUserTypeInput = document.getElementById('reg_user_type');
        
        loginTab.addEventListener('click', () => {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginForm.classList.add('active');
            registerForm.classList.remove('active');
        });
        
        registerTab.addEventListener('click', () => {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerForm.classList.add('active');
            loginForm.classList.remove('active');
        });
        
        // User type selection
        const userTypeBtns = document.querySelectorAll('.user-type-btn');
        userTypeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                userTypeBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                regUserTypeInput.value = btn.dataset.type;
            });
        });
        
        // Auto-switch to register tab if there's a register error
        <?php if ($register_error): ?>
        registerTab.click();
        <?php endif; ?>
    </script>
</body>
</html> 