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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/modern-theme.css">
    <link rel="stylesheet" href="../css/theme.css">
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-background">
        <div class="particles"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
            <div class="shape shape-5"></div>
        </div>
    </div>

    <div class="login-outer-container">
        <div class="login-main-card">
            <!-- Left: Logo and System Title -->
            <div class="login-left">
                <div class="login-logo-box">
                    <div class="logo-container">
                        <img src="../pics/rso-bg.png" alt="UC Logo" class="login-logo-img">
                        <div class="logo-glow"></div>
                    </div>
                    <div class="login-logo-title">UC RSO</div>
                    <div class="login-logo-desc">Research Management System</div>
                    <div class="login-features">
                        <div class="feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Track Research Progress</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Collaborate Seamlessly</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure & Reliable</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right: Login/Register Forms -->
            <div class="login-right">
                <div class="login-tabs">
                    <button class="login-tab active" id="loginTab">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </button>
                    <button class="login-tab" id="registerTab">
                        <i class="fas fa-user-plus"></i>
                        <span>Register</span>
                    </button>
                </div>
                <!-- Login Form -->
                <div class="login-form active" id="loginForm">
                    <?php if ($login_error): ?>
                        <div class="form-error animate-slide-in">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($login_error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($register_success): ?>
                        <div class="form-success animate-slide-in">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($register_success); ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" action="" class="animated-form">
                        <div class="form-group">
                            <div class="input-container">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="text" id="email" name="email" required placeholder="Enter your email or username" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <div class="input-focus-border"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-container">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="password" name="password" required placeholder="Enter your password">
                                <button type="button" class="password-toggle" id="passwordToggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="input-focus-border"></div>
                            </div>
                        </div>
                        <button type="submit" class="login-btn" id="loginSubmitBtn">
                            <span class="btn-text">Login</span>
                            <span class="btn-loader">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </form>
                    <div class="forgot-password">
                        <a href="#" class="forgot-link">
                            <i class="fas fa-key"></i>
                            Forgot Password?
                        </a>
                    </div>
                </div>
                <!-- Register Form -->
                <div class="login-form" id="registerForm">
                    <?php if ($register_error): ?>
                        <div class="form-error animate-slide-in">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($register_error); ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" action="" class="animated-form">
                        <input type="hidden" name="reg_user_type" id="reg_user_type" value="faculty">
                        <div class="form-group">
                            <div class="input-container">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="reg_full_name" name="reg_full_name" required placeholder="Enter your full name" value="<?php echo isset($_POST['reg_full_name']) ? htmlspecialchars($_POST['reg_full_name']) : ''; ?>">
                                <div class="input-focus-border"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-container">
                                <i class="fas fa-building input-icon"></i>
                                <input type="text" id="reg_department" name="reg_department" required placeholder="Enter your department" value="<?php echo isset($_POST['reg_department']) ? htmlspecialchars($_POST['reg_department']) : ''; ?>">
                                <div class="input-focus-border"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-container">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" id="reg_email" name="reg_email" required placeholder="Enter your email" value="<?php echo isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : ''; ?>">
                                <div class="input-focus-border"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-container">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="reg_password" name="reg_password" required placeholder="Enter your password">
                                <button type="button" class="password-toggle" id="regPasswordToggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="input-focus-border"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="user-type-label">Select User Type</label>
                            <div class="user-type-selector">
                                <button type="button" class="user-type-btn active" data-type="faculty">
                                    <div class="btn-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="btn-content">
                                        <span class="btn-title">Faculty Member</span>
                                        <span class="btn-desc">Academic Staff</span>
                                    </div>
                                </button>
                                <button type="button" class="user-type-btn" data-type="rso">
                                    <div class="btn-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="btn-content">
                                        <span class="btn-title">RSO Member</span>
                                        <span class="btn-desc">Research Staff</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <button type="submit" name="register" class="login-btn" id="registerSubmitBtn">
                            <span class="btn-text">Create Account</span>
                            <span class="btn-loader">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0072bc 0%, #2196f3 100%);
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background (subtle, less distracting) */
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }
        .particles, .floating-shapes { display: none; } /* Hide for clean look */

        .login-outer-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-main-card {
            display: flex;
            width: 900px;
            max-width: 98vw;
            min-height: 500px;
            background: #f8fafc;
            border-radius: 28px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            overflow: hidden;
            position: relative;
            border: none;
            /* Remove any transform/scale on hover */
            transform: scale(1);
            transition: none;
        }

        /* Left: Green logo/title bar */
        .login-left {
            flex: 1.1;
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 260px;
            padding: 32px 0;
        }
        .login-logo-box {
            text-align: center;
        }
        .login-logo-img {
            width: 110px;
            height: 110px;
            object-fit: contain;
            margin-bottom: 18px;
            border-radius: 16px;
            background: #fff;
            padding: 6px;
            box-shadow: 0 2px 8px #0002;
        }
        .login-logo-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px #0003, 0 1px 0 #ffd700;
        }
        .login-logo-desc {
            font-size: 1.08rem;
            color: #ffd700;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .login-features {
            margin-top: 32px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        .feature-item i {
            color: #ffd700;
            font-size: 1.3rem;
        }

        /* Right: Card with tabs and forms */
        .login-right {
            flex: 1.5;
            background: #fff;
            padding: 40px 32px 32px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-radius: 0 28px 28px 0;
        }
        .login-tabs {
            display: flex;
            margin-bottom: 24px;
            border-bottom: 2px solid #ffb300;
            background: none;
        }
        .login-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            background: none;
            border: none;
            color: #218838;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 4px solid transparent;
            font-size: 1.08rem;
            letter-spacing: 0.5px;
            border-radius: 8px 8px 0 0;
        }
        .login-tab.active {
            color: #fff;
            background: linear-gradient(90deg, #ffb300 0%, #ffea70 100%);
            border-bottom-color: #ffb300;
            box-shadow: 0 2px 8px #ffb30033;
        }
        .login-form {
            display: none;
        }
        .login-form.active {
            display: block;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #218838;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1.5px solid #bfc6e0;
            background: #f8fafc;
            font-size: 1rem;
            color: #1e293b;
            outline: none;
            transition: border 0.2s;
        }
        .form-group input:focus {
            border-color: #2196f3;
            background: #fff;
        }
        .login-btn {
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(90deg, #ffb300 0%, #ffea70 100%);
            color: #218838;
            border: none;
            border-radius: 8px;
            font-size: 1.08rem;
            font-weight: 800;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 2px 8px #ffb30033;
            transition: background 0.2s, color 0.2s;
        }
        .login-btn:hover {
            background: #218838;
            color: #fff;
        }
        .form-error {
            color: #d32f2f;
            background: #ffeaea;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 0.98rem;
        }
        .form-success {
            color: #388e3c;
            background: #eaffea;
            border: 1px solid #b2dfdb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 0.98rem;
        }
        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }
        .forgot-password a {
            color: #2196f3;
            font-size: 0.97rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot-password a:hover {
            color: #ffb300;
        }
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
            border: 2px solid #bfc6e0;
            border-radius: 8px;
            background: #f7f8fa;
            color: #218838;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .user-type-btn:hover {
            border-color: #ffb300;
            color: #ffb300;
        }
        .user-type-btn.active {
            border-color: #ffb300;
            background: #fffbe6;
            color: #218838;
        }
        .user-type-btn i {
            font-size: 1.25rem;
        }
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-container input {
            width: 100%;
            padding: 12px 40px 12px 14px; /* Add right padding for the icon */
            border-radius: 8px;
            border: 1.5px solid #bfc6e0;
            background: #f8fafc;
            font-size: 1rem;
            color: #1e293b;
            outline: none;
            transition: border 0.2s;
        }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #218838;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 2;
            visibility: hidden;
            padding: 0;
            height: 24px;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 900px) {
            .login-main-card {
                flex-direction: column;
                width: 98vw;
                min-width: 0;
                border-radius: 24px;
            }
            .login-left, .login-right {
                min-width: 0;
                padding: 24px 12px;
            }
            .login-left {
                border-right: none;
                border-bottom: 2px solid #ffb300;
                border-radius: 24px 24px 0 0;
            }
            .login-right {
                border-radius: 0 0 24px 24px;
            }
        }
        @media (max-width: 600px) {
            .login-main-card {
                width: 100vw;
                border-radius: 0;
            }
            .login-left, .login-right {
                padding: 16px 4vw;
            }
        }
    </style>

    <script src="../js/theme.js"></script>
    <script>
        // Enhanced JavaScript with animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching with smooth animations
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const regUserTypeInput = document.getElementById('reg_user_type');
            
            function switchTab(activeTab, activeForm, inactiveTab, inactiveForm) {
                inactiveTab.classList.remove('active');
                activeTab.classList.add('active');
                
                inactiveForm.style.display = 'none';
                activeForm.style.display = 'block';
                
                // Trigger animation
                setTimeout(() => {
                    activeForm.classList.add('active');
                }, 10);
            }
            
            loginTab.addEventListener('click', () => {
                switchTab(loginTab, loginForm, registerTab, registerForm);
            });
            
            registerTab.addEventListener('click', () => {
                switchTab(registerTab, registerForm, loginTab, loginForm);
            });
            
            // Password visibility toggle
            const passwordToggle = document.getElementById('passwordToggle');
            const regPasswordToggle = document.getElementById('regPasswordToggle');
            const passwordInput = document.getElementById('password');
            const regPasswordInput = document.getElementById('reg_password');
            
            function togglePasswordVisibility(toggleBtn, input) {
                toggleBtn.addEventListener('click', () => {
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    const icon = toggleBtn.querySelector('i');
                    icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                });
            }
            
            togglePasswordVisibility(passwordToggle, passwordInput);
            togglePasswordVisibility(regPasswordToggle, regPasswordInput);

            // Show/hide password icon only if input has value
            function togglePasswordIcon(input, toggleBtn) {
                input.addEventListener('input', function() {
                    if (input.value.length > 0) {
                        toggleBtn.style.visibility = 'visible';
                    } else {
                        toggleBtn.style.visibility = 'hidden';
                    }
                });
                // Initialize on page load
                if (input.value.length > 0) {
                    toggleBtn.style.visibility = 'visible';
                } else {
                    toggleBtn.style.visibility = 'hidden';
                }
            }
            togglePasswordIcon(passwordInput, passwordToggle);
            togglePasswordIcon(regPasswordInput, regPasswordToggle);
            
            // User type selection with enhanced animations
            const userTypeBtns = document.querySelectorAll('.user-type-btn');
            userTypeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    userTypeBtns.forEach(b => {
                        b.classList.remove('active');
                        b.style.transform = 'scale(1)';
                    });
                    btn.classList.add('active');
                    btn.style.transform = 'scale(1.05)';
                    regUserTypeInput.value = btn.dataset.type;
                    
                    // Reset transform after animation
                    setTimeout(() => {
                        btn.style.transform = '';
                    }, 200);
                });
            });
            
            // Form submission with loading animation
            const loginSubmitBtn = document.getElementById('loginSubmitBtn');
            const registerSubmitBtn = document.getElementById('registerSubmitBtn');
            
            function addLoadingState(btn) {
                btn.classList.add('loading');
                btn.disabled = true;
            }
            
            // Add loading state to forms
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('.login-btn');
                    if (submitBtn) {
                        addLoadingState(submitBtn);
                    }
                });
            });
            
            // Input focus animations
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Auto-switch to register tab if there's a register error
            <?php if ($register_error): ?>
            registerTab.click();
            <?php endif; ?>
            
            // Add floating animation to logo
            const logoImg = document.querySelector('.login-logo-img');
            if (logoImg) {
                logoImg.style.animation = 'float 3s ease-in-out infinite';
            }
            
            // Remove mainCard hover effect
            // const mainCard = document.querySelector('.login-main-card');
            // mainCard.addEventListener('mouseenter', function() {
            //     this.style.transform = 'scale(1.02)';
            // });
            // mainCard.addEventListener('mouseleave', function() {
            //     this.style.transform = 'scale(1)';
            // });
            
            // Add ripple effect to buttons
            function createRipple(event) {
                const button = event.currentTarget;
                const ripple = document.createElement('span');
                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = event.clientX - rect.left - size / 2;
                const y = event.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                button.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            }
            
            // Add ripple effect to all buttons
            document.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', createRipple);
            });
            
            // Add CSS for ripple effect
            const style = document.createElement('style');
            style.textContent = `
                .ripple {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    transform: scale(0);
                    animation: ripple-animation 0.6s linear;
                    pointer-events: none;
                }
                
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                button {
                    position: relative;
                    overflow: hidden;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html> 