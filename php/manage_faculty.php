<?php
session_start();
// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: loginpage.php');
    exit;
}
// Check admin login
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: loginpage.php');
    exit;
}
// manage_faculty.php - Admin management of faculty accounts
$users_file = 'users.csv';
$delete_message = '';

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_email = $_GET['delete'];
    if (file_exists($users_file)) {
        $rows = [];
        $deleted = false;
        $file = fopen($users_file, 'r');
        while (($data = fgetcsv($file)) !== false) {
            if ($data[0] === $delete_email && $data[2] === 'faculty') {
                $deleted = true;
                continue; // skip this row
            }
            $rows[] = $data;
        }
        fclose($file);
        $file = fopen($users_file, 'w');
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
        $delete_message = $deleted ? 'Faculty account deleted.' : 'Account not found.';
    }
}

// Read all faculty users
$faculty = [];
if (file_exists($users_file)) {
    $file = fopen($users_file, 'r');
    while (($data = fgetcsv($file)) !== false) {
        if (isset($data[2]) && $data[2] === 'faculty') {
            $faculty[] = $data;
        }
    }
    fclose($file);
}

// Data Collection Tools Management
$dct_file = 'data_collection_tools.csv';
$dct_entries = [];
if (file_exists($dct_file)) {
    $fp = fopen($dct_file, 'r');
    while ($row = fgetcsv($fp)) {
        $dct_entries[] = $row;
    }
    fclose($fp);
}

// Handle DCT delete
if (isset($_POST['delete_dct']) && isset($_POST['dct_index'])) {
    $index = (int)$_POST['dct_index'];
    if (isset($dct_entries[$index])) {
        array_splice($dct_entries, $index, 1);
        $fp = fopen($dct_file, 'w');
        foreach ($dct_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle DCT edit save
if (isset($_POST['save_dct_edit']) && isset($_POST['dct_index'])) {
    $index = (int)$_POST['dct_index'];
    $faculty = $_POST['faculty'] ?? '';
    $degree = $_POST['degree'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $title = $_POST['title'] ?? '';
    $ownership = $_POST['ownership'] ?? '';
    $presented = $_POST['presented'] ?? '';
    $published = $_POST['published'] ?? '';
    $journal = $_POST['journal'] ?? '';
    if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
        $dct_entries[$index] = [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal];
        $fp = fopen($dct_file, 'w');
        foreach ($dct_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$dct_edit_index = isset($_GET['dct_edit']) ? (int)$_GET['dct_edit'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Faculty Accounts</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>
        body {
            background: #f3f4ef;
            font-family: 'Montserrat', Arial, sans-serif;
        }
        .admin-flex {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            margin-top: 60px;
        }
        .admin-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 36px 36px 28px 36px;
            min-width: 420px;
            max-width: 900px;
            margin-bottom: 30px;
        }
        .admin-card .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .admin-card .logo img {
            width: 60px;
            margin-bottom: 10px;
        }
        .admin-card h1 {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: #6a7a5e;
            margin-bottom: 24px;
        }
        .admin-card table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fafaf7;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .admin-card th, .admin-card td {
            padding: 12px 10px;
            border-bottom: 1px solid #e3e3d9;
            text-align: left;
        }
        .admin-card th {
            background: #e9ecdf;
            color: #5a6b4e;
            font-weight: 700;
        }
        .admin-card tr:last-child td {
            border-bottom: none;
        }
        .admin-card td {
            background: #fafaf7;
        }
        .admin-card .delete-btn, .admin-card a, .admin-card button {
            font-size: 1rem;
            border: none;
            background: none;
            color: #b94a48;
            cursor: pointer;
            text-decoration: underline;
            margin-right: 8px;
        }
        .admin-card .delete-btn:hover, .admin-card a:hover, .admin-card button:hover {
            color: #a94442;
        }
        .admin-card input[type="text"], .admin-card input[type="date"] {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #cfd8c2;
            border-radius: 6px;
            background: #f7faf3;
            font-size: 1rem;
        }
        .admin-card form {
            margin: 0;
        }
        @media (max-width: 1100px) {
            .admin-flex { flex-direction: column; align-items: center; }
            .admin-card { min-width: 320px; max-width: 98vw; }
        }
        .logout-btn {
            background:#b94a48;
            color:#fff;
            border:none;
            padding:8px 18px;
            border-radius:6px;
            font-size:1rem;
            cursor:pointer;
            position: fixed;
            top: 24px;
            right: 40px;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <form method="post" style="margin:0; padding:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
    <a href="../index.php" class="back-btn">Back to Dashboard</a>
    <div class="admin-flex">
        <div class="admin-card">
            <div class="logo">
                <img src="../pics/rso-bg.png" alt="UC Logo">
            </div>
            <h1>Manage Faculty Accounts</h1>
            <?php if ($delete_message): ?>
                <div style="color:green; text-align:center; margin-bottom:10px;"> <?php echo htmlspecialchars($delete_message); ?> </div>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($faculty) === 0): ?>
                        <tr><td colspan="3" style="text-align:center;">No faculty accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($faculty as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user[0]); ?></td>
                                <td><?php echo htmlspecialchars($user[2]); ?></td>
                                <td><a class="delete-btn" href="?delete=<?php echo urlencode($user[0]); ?>" onclick="return confirm('Delete this faculty account?');">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="admin-card">
            <div class="logo"></div>
            <h1>Manage Data Collection Tools</h1>
            <table>
                <thead>
                    <tr>
                        <th>Name of Faculty</th>
                        <th>Degree</th>
                        <th>Sex</th>
                        <th>Research Title</th>
                        <th>Ownership</th>
                        <th>Date & Venue Presented</th>
                        <th>Date Published</th>
                        <th>Journal Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dct_entries as $i => $entry): ?>
                        <?php if ($dct_edit_index === $i): ?>
                            <tr style="background:#ffeeba;">
                                <form method="post" action="">
                                    <td><input type="text" name="faculty" value="<?php echo htmlspecialchars($entry[0]); ?>" required></td>
                                    <td><input type="text" name="degree" value="<?php echo htmlspecialchars($entry[1]); ?>" required></td>
                                    <td><input type="text" name="sex" value="<?php echo htmlspecialchars($entry[2]); ?>" required></td>
                                    <td><input type="text" name="title" value="<?php echo htmlspecialchars($entry[3]); ?>" required></td>
                                    <td><input type="text" name="ownership" value="<?php echo htmlspecialchars($entry[4]); ?>" required></td>
                                    <td><input type="text" name="presented" value="<?php echo htmlspecialchars($entry[5]); ?>" required></td>
                                    <td><input type="text" name="published" value="<?php echo htmlspecialchars($entry[6]); ?>" required></td>
                                    <td><input type="text" name="journal" value="<?php echo htmlspecialchars($entry[7]); ?>" required></td>
                                    <td>
                                        <input type="hidden" name="dct_index" value="<?php echo $i; ?>">
                                        <button type="submit" name="save_dct_edit">Save</button>
                                        <a href="manage_faculty.php">Cancel</a>
                                    </td>
                                </form>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry[0]); ?></td>
                                <td><?php echo htmlspecialchars($entry[1]); ?></td>
                                <td><?php echo htmlspecialchars($entry[2]); ?></td>
                                <td><?php echo htmlspecialchars($entry[3]); ?></td>
                                <td><?php echo htmlspecialchars($entry[4]); ?></td>
                                <td><?php echo htmlspecialchars($entry[5]); ?></td>
                                <td><?php echo htmlspecialchars($entry[6]); ?></td>
                                <td><?php echo htmlspecialchars($entry[7]); ?></td>
                                <td>
                                    <a href="manage_faculty.php?dct_edit=<?php echo $i; ?>">Edit</a> |
                                    <form method="post" action="" style="display:inline;">
                                        <input type="hidden" name="dct_index" value="<?php echo $i; ?>">
                                        <button type="submit" name="delete_dct" class="delete-btn" onclick="return confirm('Delete this entry?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 