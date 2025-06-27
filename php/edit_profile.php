<?php
session_start();

// Check if user is logged in
if (empty($_SESSION['logged_in'])) {
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

// Get current user data
$current_full_name = $_SESSION['user_full_name'] ?? '';
$current_department = $_SESSION['user_department'] ?? '';
$current_email = $_SESSION['user_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Research Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #6a7a5e;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .section-title {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.2em;
            font-weight: 600;
        }
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="../index.php" class="back-link">← Back to Dashboard</a>
        
        <div class="logo" style="text-align: center; margin-bottom: 30px;">
            <img src="../pics/rso-bg.png" alt="UC Logo" style="width: 80px; height: auto;">
            <h1>Edit Profile</h1>
        </div>
        
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-section">
                <div class="section-title">Personal Information</div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($current_full_name); ?>">
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" required 
                           value="<?php echo htmlspecialchars($current_department); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($current_email); ?>">
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">Change Password (Optional)</div>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" 
                           placeholder="Enter current password to change password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" 
                           placeholder="Enter new password (min 6 characters)">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm new password">
                </div>
            </div>
            
            <button type="submit" class="login-btn">Update Profile</button>
        </form>
    </div>
</body>
</html> 