<?php
session_start();

// Check if user is logged in
if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: loginpage.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_full_name = trim($_POST['full_name'] ?? '');
    $new_department = trim($_POST['department'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($new_full_name) || empty($new_department) || empty($new_email)) {
        $error_message = 'Full name, department, and email are required.';
    } else {
        $users_file = 'users.csv';
        $user_email = $_SESSION['user_email'];
        $user_updated = false;
        
        // Read all users
        $users = [];
        if (file_exists($users_file)) {
            $file = fopen($users_file, 'r');
            while (($data = fgetcsv($file)) !== false) {
                $users[] = $data;
            }
            fclose($file);
        }
        
        // Find and update the current user
        foreach ($users as $index => $user_data) {
            if ($user_data[0] === $user_email) {
                // Check if email is being changed and if it already exists
                if ($new_email !== $user_email) {
                    foreach ($users as $other_user) {
                        if ($other_user[0] === $new_email) {
                            $error_message = 'Email already exists. Please choose a different email.';
                            break 2;
                        }
                    }
                }
                
                // Verify current password if changing password
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error_message = 'Current password is required to change password.';
                        break;
                    }
                    if (!password_verify($current_password, $user_data[1])) {
                        $error_message = 'Current password is incorrect.';
                        break;
                    }
                    if ($new_password !== $confirm_password) {
                        $error_message = 'New passwords do not match.';
                        break;
                    }
                    if (strlen($new_password) < 6) {
                        $error_message = 'New password must be at least 6 characters long.';
                        break;
                    }
                    $users[$index][1] = password_hash($new_password, PASSWORD_DEFAULT);
                }
                
                // Update user information
                $users[$index][0] = $new_email;
                $users[$index][3] = $new_full_name;
                $users[$index][4] = $new_department;
                
                // Write back to file
                $file = fopen($users_file, 'w');
                foreach ($users as $user) {
                    fputcsv($file, $user);
                }
                fclose($file);
                
                // Update session
                $_SESSION['user_email'] = $new_email;
                $_SESSION['user_full_name'] = $new_full_name;
                $_SESSION['user_department'] = $new_department;
                
                $user_updated = true;
                $success_message = 'Profile updated successfully!';
                break;
            }
        }
        
        if (!$user_updated && empty($error_message)) {
            $error_message = 'User not found.';
        }
    }
}

// Handle profile picture upload
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_FILES['profile_picture']) &&
    $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK
) {
    $uploads_dir = __DIR__ . '/../uploads/profile_pictures/';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }
    $tmp_name = $_FILES['profile_picture']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    if (in_array($ext, $allowed_ext)) {
        $email = $_SESSION['user_email'];
        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $email) . '_' . time() . '.' . $ext;
        $destination = $uploads_dir . $filename;
        if (move_uploaded_file($tmp_name, $destination)) {
            $relative_path = '../uploads/profile_pictures/' . $filename;
            $_SESSION['profile_picture'] = $relative_path;
            // Update users.csv
            $users_file = __DIR__ . '/users.csv';
            $users = [];
            if (file_exists($users_file)) {
                $file = fopen($users_file, 'r');
                while (($data = fgetcsv($file)) !== false) {
                    $users[] = $data;
                }
                fclose($file);
            }
            foreach ($users as $i => $user) {
                if ($user[0] === $email) {
                    $users[$i][5] = $relative_path; // Assume column 5 is profile picture
                }
            }
            $file = fopen($users_file, 'w');
            foreach ($users as $user) {
                fputcsv($file, $user);
            }
            fclose($file);
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get current user data
$current_full_name = $_SESSION['user_full_name'] ?? '';
$current_department = $_SESSION['user_department'] ?? '';
$current_email = $_SESSION['user_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - UC RSO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/modern-theme.css">
  <link rel="stylesheet" href="../css/theme.css">
    <style>
        .profile-section {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .profile-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            text-align: center;
            height: fit-content;
        }
        
        .profile-avatar {
            margin-bottom: 1.5rem;
        }
        
        .profile-avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .profile-avatar img:hover {
            transform: scale(1.05);
        }
        
        .upload-section {
            margin-top: 1.5rem;
        }
        
        .upload-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .upload-btn:hover {
            background: var(--primary-hover);
        }
        
        .upload-note {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }
        
        .details-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--card-shadow);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .password-section {
            grid-column: 1 / -1;
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
            margin-top: 1rem;
        }
        
        .password-section h4 {
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .password-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }
        
        .save-btn {
            grid-column: 1 / -1;
            margin-top: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .profile-section {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .password-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <img src="../pics/rso-bg.png" alt="UC Logo">
                <span>UC RSO</span>
            </div>
            <nav class="nav">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="Research  Capacity Buildings Activities.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Research Capacity</span>
                </a>
                <a href="Data Collection Tools.php" class="nav-link">
                    <i class="fas fa-database"></i>
                    <span>Data Collection</span>
                </a>
                <a href="Ethicss Reviewed Protocols.php" class="nav-link">
                    <i class="fas fa-shield-alt"></i>
                    <span>Ethics Protocols</span>
                </a>
                <a href="Publication and Presentation.php" class="nav-link">
                    <i class="fas fa-book"></i>
                    <span>Publications</span>
                </a>
                <a href="KPI records.php" class="nav-link">
                    <i class="fas fa-target"></i>
                    <span>KPI Records</span>
                </a>
                  </nav>
      
      <!-- Theme Toggle -->
      <button class="theme-toggle" title="Toggle Theme">
        <i class="fas fa-moon"></i>
      </button>
      
      <!-- Profile Menu -->
            <div class="profile-menu" id="profileMenu">
                <button class="profile-btn" id="profileBtn">
                    <?php
                        $profile_picture = $_SESSION['profile_picture'] ?? '';
                        $profile_picture_path = '';
                        if (!empty($profile_picture)) {
                            if (strpos($profile_picture, '../') === 0) {
                                $full_path = __DIR__ . '/' . $profile_picture;
                                if (file_exists($full_path)) {
                                    $profile_picture_path = $profile_picture;
                                }
                            } else {
                                $profile_picture_path = $profile_picture;
                            }
                        }
                    ?>
                    <?php if ($profile_picture_path): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Profile" class="profile-img">
                    <?php else: ?>
                        <img src="../pics/rso-bg.png" alt="Profile" class="profile-img">
                    <?php endif; ?>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-info">
                        <div class="profile-name"><?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'User'); ?></div>
                        <div class="profile-role"><?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Department'); ?></div>
                        <div class="profile-type"><?php echo htmlspecialchars(ucfirst($_SESSION['user_type'] ?? '')); ?></div>
                    </div>
                    <div class="profile-actions">
                        <a href="edit_profile.php" class="profile-action active">
                            <i class="fas fa-user-edit"></i>
                            Edit Profile
                        </a>
                        <form method="post" class="logout-form">
                            <button type="submit" name="logout" class="profile-action logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h1>Edit Profile</h1>
                    <p>Update your account information and profile picture</p>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Section -->
            <div class="profile-section">
                <!-- Profile Picture Card -->
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php
                            $profile_pic_to_show = '../pics/rso-bg.png';
                            if (!empty($_SESSION['profile_picture']) && $_SESSION['profile_picture'] !== '../pics/rso-bg.png') {
                                $profile_pic_to_show = htmlspecialchars($_SESSION['profile_picture']);
                            } elseif (isset($current_profile_pic) && $current_profile_pic && $current_profile_pic !== '../pics/rso-bg.png') {
                                $profile_pic_to_show = htmlspecialchars($current_profile_pic);
                            }
                        ?>
                        <img src="<?php echo $profile_pic_to_show; ?>" alt="Profile Picture" id="profilePicPreview">
                    </div>
                    
                    <h3>Profile Picture</h3>
                    <p class="text-muted">Update your profile picture</p>
                    
                    <div class="upload-section">
                        <form method="post" enctype="multipart/form-data" id="profilePicForm">
                            <input type="file" name="profile_picture" id="profilePicInput" accept="image/*" style="display: none;" onchange="document.getElementById('profilePicForm').submit();">
                            <button type="button" class="upload-btn" onclick="document.getElementById('profilePicInput').click();">
                                <i class="fas fa-upload"></i>
                                Upload New Image
                            </button>
                        </form>
                        <div class="upload-note">
                            JPG or PNG files, max 5MB
                        </div>
                    </div>
                </div>

                <!-- Account Details Card -->
                <div class="details-card">
                    <h3>Account Details</h3>
                    <p class="text-muted">Update your personal information and password</p>
                    
                    <form method="post" action="" autocomplete="off" class="form-grid">
                        <div class="form-group full-width">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($current_full_name); ?>" placeholder="Enter your full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department" required value="<?php echo htmlspecialchars($current_department); ?>" placeholder="Enter your department">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($current_email); ?>" placeholder="Enter your email">
                        </div>
                        
                        <div class="password-section">
                            <h4>Change Password</h4>
                            <p class="text-muted">Leave blank if you don't want to change your password</p>
                            
                            <div class="password-grid">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min 6 characters)">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group save-btn">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../js/theme.js"></script>
    <script>
        // Profile menu toggle
        const profileMenu = document.getElementById('profileMenu');
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!profileMenu.contains(e.target)) {
                profileMenu.classList.remove('open');
            }
        });

        // Profile picture preview
        const profilePicInput = document.getElementById('profilePicInput');
        const profilePicPreview = document.getElementById('profilePicPreview');
        
        profilePicInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 