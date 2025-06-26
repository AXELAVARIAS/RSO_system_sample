<?php
// File to store entries
$data_file = __DIR__ . '/kpi_records.csv';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle delete
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $entries = [];
        if (file_exists($data_file)) {
            $fp = fopen($data_file, 'r');
            while ($row = fgetcsv($fp)) {
                $entries[] = $row;
            }
            fclose($fp);
        }
        $index = (int)$_POST['index'];
        if (isset($entries[$index])) {
            array_splice($entries, $index, 1);
            $fp = fopen($data_file, 'w');
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
        $entries = [];
        if (file_exists($data_file)) {
            $fp = fopen($data_file, 'r');
            while ($row = fgetcsv($fp)) {
                $entries[] = $row;
            }
            fclose($fp);
        }
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
        $entry = [$faculty, $period, $publications, $trainings, $presentations, $score, $performance];
        $fp = fopen($data_file, 'a');
        fputcsv($fp, $entry);
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

// On first load, if CSV is empty, populate with default entries
if (!file_exists($data_file) || filesize($data_file) === 0) {
    $fp = fopen($data_file, 'w');
    foreach ($default_entries as $entry) {
        fputcsv($fp, $entry);
    }
    fclose($fp);
}

// Read all entries
$entries = [];
if (file_exists($data_file)) {
    $fp = fopen($data_file, 'r');
    while ($row = fgetcsv($fp)) {
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
  </style>
</head>
<body>
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
    <button class="login-btn" onclick="window.location.href='loginpage.php'">Login</button>
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
  </script>
</body>
</html> 