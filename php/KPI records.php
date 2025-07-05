<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: loginpage.php');
    exit;
}
// File to store entries
$data_file = __DIR__ . '/kpi_records.csv';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read all entries (skip header)
    $entries = [];
    if (file_exists($data_file)) {
        $fp = fopen($data_file, 'r');
        $is_first_row = true;
        while ($row = fgetcsv($fp)) {
            if ($is_first_row) {
                $is_first_row = false;
                continue; // skip header
            }
            $entries[] = $row;
        }
        fclose($fp);
    }
    // Handle delete
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        if (isset($entries[$index])) {
            array_splice($entries, $index, 1);
            $fp = fopen($data_file, 'w');
            fputcsv($fp, ['Faculty Name', 'Period', 'Publications', 'Trainings', 'Presentations', 'KPI Score', 'Performance']);
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Handle edit save
    if (isset($_POST['save_edit']) && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $faculty = $_POST['faculty'] ?? '';
        $period = $_POST['period'] ?? '';
        $publications = $_POST['publications'] ?? '';
        $trainings = $_POST['trainings'] ?? '';
        $presentations = $_POST['presentations'] ?? '';
        $score = $_POST['score'] ?? '';
        $performance = $_POST['performance'] ?? '';
        if ($faculty && $period && $publications && $trainings && $presentations && $score && $performance) {
            $entries[$index] = [$faculty, $period, $publications, $trainings, $presentations, $score, $performance];
            $fp = fopen($data_file, 'w');
            fputcsv($fp, ['Faculty Name', 'Period', 'Publications', 'Trainings', 'Presentations', 'KPI Score', 'Performance']);
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Handle add
    $faculty = $_POST['faculty'] ?? '';
    $period = $_POST['period'] ?? '';
    $publications = $_POST['publications'] ?? '';
    $trainings = $_POST['trainings'] ?? '';
    $presentations = $_POST['presentations'] ?? '';
    $score = $_POST['score'] ?? '';
    $performance = $_POST['performance'] ?? '';
    if ($faculty && $period && $publications && $trainings && $presentations && $score && $performance) {
        $entries[] = [$faculty, $period, $publications, $trainings, $presentations, $score, $performance];
        $fp = fopen($data_file, 'w');
        fputcsv($fp, ['Faculty Name', 'Period', 'Publications', 'Trainings', 'Presentations', 'KPI Score', 'Performance']);
        foreach ($entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries
$default_entries = [
    ['Dr. Sarah Johnson', '2025 - Semester 1', '8', '5', '12', '9.2', 'Excellent'],
    ['Prof. Michael Chen', '2025 - Semester 1', '6', '3', '8', '8.1', 'Very Good'],
    ['Dr. Emily Rodriguez', '2025 - Semester 1', '4', '7', '6', '7.8', 'Good'],
    ['Dr. James Wilson', '2025 - Semester 1', '5', '4', '9', '8.5', 'Very Good'],
];

// Read all entries
$entries = [];
if (file_exists($data_file)) {
    $fp = fopen($data_file, 'r');
    $is_first_row = true;
    while ($row = fgetcsv($fp)) {
        if ($is_first_row) {
            $is_first_row = false;
            continue; // skip header
        }
        $entries[] = $row;
    }
    fclose($fp);
}

// Check if editing
$edit_index = null;
$edit_entry = null;
if (isset($_GET['edit'])) {
    $edit_index = (int)$_GET['edit'];
    if (isset($entries[$edit_index])) {
        $edit_entry = $entries[$edit_index];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Faculty KPI Tracking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/KPI records.css">
  <style>
    .edit-entry-form { background: #f1f1f1; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      z-index: 1000;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    body {
      margin: 0;
      padding-top: 80px;
    }
    .profile-menu {
      position: fixed;
      top: 18px;
      right: 40px;
      z-index: 1100;
      display: flex;
      align-items: center;
    }
    .profile-icon-btn {
      background: #e9ecdf;
      border: none;
      border-radius: 50%;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: background 0.2s;
      font-size: 1.7rem;
      padding: 0;
    }
    .profile-icon-btn:hover {
      background: #d2d8c2;
    }
    .profile-dropdown {
      display: none;
      position: absolute;
      top: 54px;
      right: 0;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.13);
      min-width: 240px;
      padding: 16px 20px 12px 20px;
      text-align: left;
      animation: fadeIn 0.2s;
      box-sizing: border-box;
    }
    .profile-menu.open .profile-dropdown {
      display: block;
    }
    .profile-dropdown form {
      margin: 0;
      padding: 0 0;
    }
    .profile-dropdown button {
      background: #b94a48;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 7px 18px;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 6px;
      width: 100%;
      text-align: center;
      transition: background 0.2s;
    }
    .profile-dropdown button:hover {
      background: #a94442;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
<div class="profile-menu" id="profileMenu">
    <button class="profile-icon-btn" id="profileIconBtn" aria-label="Profile">
      <!-- SVG user icon -->
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6a7a5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-2.5 3.5-4 8-4s8 1.5 8 4"/></svg>
    </button>
    <div class="profile-dropdown" id="profileDropdown">
      <div style="font-weight: 600; margin-bottom: 4px; text-align: left;">
        <?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'User'); ?>
      </div>
      <div style="font-size:0.9em; color:#6a7a5e; margin-bottom:2px; text-align: left;">
        <?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Department'); ?>
      </div>
      <div style="font-size:0.85em; color:#9a9a8a; text-align: left;">
        <?php echo htmlspecialchars(ucfirst($_SESSION['user_type'] ?? '')); ?>
      </div>
      <div style="border-top: 1px solid #eee; margin: 10px 0; padding-top: 10px; text-align: left;">
        <a href="edit_profile.php" style="display: block; color: #6a7a5e; text-decoration: none; padding: 8px 0; font-size: 0.9em; text-align: left;">Edit Profile</a>
      </div>
      <form method="post">
        <button type="submit" name="logout">Logout</button>
      </form>
    </div>
  </div>
  <header>
    <div class="logo">
      <img src="../pics/rso-bg.png" alt="UC Logo">
      UC RSO
    </div>
    <nav>
      <a href="../index.php">Dashboard</a>
      <a href="Research  Capacity Buildings Activities.php">Research Capacity Building</a>
      <a href="Data Collection Tools.php">Data Collection Tools</a>
      <a href="Ethicss Reviewed Protocols.php">Ethics Reviewed Protocols</a>
      <a href="Publication and Presentation.php">Publications and Presentations</a>
      <a href="KPI records.php" class="active">KPI Records</a>
    </nav>
    
  </header>
  <div class="dashboard-bg">
    <div class="container">
      <h1>Faculty KPI Tracking</h1>
      <div class="subtitle">Monitor academic performance indicators and faculty achievements</div>
      <div class="actions">
        <button class="btn add" id="showAddForm">+ Add New Entry</button>
      </div>
      <form class="add-entry-form" id="addEntryForm" method="post" action="" style="display:none; background:#f9f9f9; padding:20px; border-radius:8px; margin-bottom:20px;">
        <label>Faculty Name:<br><input type="text" name="faculty" required></label><br>
        <label>Period:<br><input type="text" name="period" required></label><br>
        <label>Publications:<br><input type="number" name="publications" min="0" required></label><br>
        <label>Trainings:<br><input type="number" name="trainings" min="0" required></label><br>
        <label>Presentations:<br><input type="number" name="presentations" min="0" required></label><br>
        <label>KPI Score:<br><input type="number" name="score" min="0" max="10" step="0.1" required></label><br>
        <label>Performance:<br>
          <select name="performance" required>
            <option value="Excellent">Excellent</option>
            <option value="Very Good">Very Good</option>
            <option value="Good">Good</option>
            <option value="Satisfactory">Satisfactory</option>
            <option value="Needs Improvement">Needs Improvement</option>
          </select>
        </label><br>
        <button type="submit" class="btn">Add Entry</button>
        <button type="button" class="btn" id="cancelAddForm">Cancel</button>
      </form>
      <div class="panel">
        <h2>KPI Performance Overview</h2>
        <div class="table-container">
          <table id="kpiTable">
            <thead>
              <tr>
                <th>Faculty Name</th>
                <th>Period</th>
                <th>Publications</th>
                <th>Trainings</th>
                <th>Presentations</th>
                <th>KPI Score</th>
                <th>Performance</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $i => $entry): ?>
              <tr<?php if ($edit_index === $i) echo ' style="background:#ffeeba;"'; ?>>
                <td data-label="Faculty Name"><?php echo htmlspecialchars($entry[0]); ?></td>
                <td data-label="Period"><?php echo htmlspecialchars($entry[1]); ?></td>
                <td data-label="Publications"><?php echo htmlspecialchars($entry[2]); ?></td>
                <td data-label="Trainings"><?php echo htmlspecialchars($entry[3]); ?></td>
                <td data-label="Presentations"><?php echo htmlspecialchars($entry[4]); ?></td>
                <td data-label="KPI Score"><span class="score"><?php echo htmlspecialchars($entry[5]); ?></span></td>
                <td data-label="Performance"><span class="performance"><?php echo htmlspecialchars($entry[6]); ?></span></td>
                <td>
                  <form method="get" action="" style="display:inline;">
                    <input type="hidden" name="edit" value="<?php echo $i; ?>">
                    <button type="submit" class="btn">Edit</button>
                  </form>
                  <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                    <input type="hidden" name="index" value="<?php echo $i; ?>">
                    <button type="submit" name="delete" class="btn">Delete</button>
                  </form>
                </td>
              </tr>
              <?php if ($edit_index === $i && $edit_entry): ?>
              <tr>
                <td colspan="8">
                  <form class="edit-entry-form" method="post" action="">
                    <input type="hidden" name="save_edit" value="1">
                    <input type="hidden" name="index" value="<?php echo $edit_index; ?>">
                    <label>Faculty Name:<br><input type="text" name="faculty" value="<?php echo htmlspecialchars($edit_entry[0]); ?>" required></label><br>
                    <label>Period:<br><input type="text" name="period" value="<?php echo htmlspecialchars($edit_entry[1]); ?>" required></label><br>
                    <label>Publications:<br><input type="number" name="publications" min="0" value="<?php echo htmlspecialchars($edit_entry[2]); ?>" required></label><br>
                    <label>Trainings:<br><input type="number" name="trainings" min="0" value="<?php echo htmlspecialchars($edit_entry[3]); ?>" required></label><br>
                    <label>Presentations:<br><input type="number" name="presentations" min="0" value="<?php echo htmlspecialchars($edit_entry[4]); ?>" required></label><br>
                    <label>KPI Score:<br><input type="number" name="score" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($edit_entry[5]); ?>" required></label><br>
                    <label>Performance:<br>
                      <select name="performance" required>
                        <option value="Excellent" <?php if ($edit_entry[6]==='Excellent') echo 'selected'; ?>>Excellent</option>
                        <option value="Very Good" <?php if ($edit_entry[6]==='Very Good') echo 'selected'; ?>>Very Good</option>
                        <option value="Good" <?php if ($edit_entry[6]==='Good') echo 'selected'; ?>>Good</option>
                        <option value="Satisfactory" <?php if ($edit_entry[6]==='Satisfactory') echo 'selected'; ?>>Satisfactory</option>
                        <option value="Needs Improvement" <?php if ($edit_entry[6]==='Needs Improvement') echo 'selected'; ?>>Needs Improvement</option>
                      </select>
                    </label><br>
                    <button type="submit" class="btn">Save Changes</button>
                  </form>
                </td>
              </tr>
              <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.getElementById('showAddForm').onclick = function() {
      document.getElementById('addEntryForm').style.display = 'block';
    };
    document.getElementById('cancelAddForm').onclick = function() {
      document.getElementById('addEntryForm').style.display = 'none';
    };
    const profileMenu = document.getElementById('profileMenu');
    const profileIconBtn = document.getElementById('profileIconBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    document.addEventListener('click', function(e) {
      if (profileMenu.contains(e.target)) {
        profileMenu.classList.toggle('open');
      } else {
        profileMenu.classList.remove('open');
      }
    });
  </script>
</body>
</html> 