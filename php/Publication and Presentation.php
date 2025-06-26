<?php
// File to store entries
$data_file = __DIR__ . '/publication_presentation.csv';

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
        $date = $_POST['date'] ?? '';
        $faculty = $_POST['faculty'] ?? '';
        $title = $_POST['title'] ?? '';
        $department = $_POST['department'] ?? '';
        $subsidy = $_POST['subsidy'] ?? '';
        $status = $_POST['status'] ?? '';
        $locality = $_POST['locality'] ?? '';
        if ($date && $faculty && $title && $department && $subsidy && $status && $locality) {
            $entries[$index] = [$date, $faculty, $title, $department, $subsidy, $status, $locality];
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
    $date = $_POST['date'] ?? '';
    $faculty = $_POST['faculty'] ?? '';
    $title = $_POST['title'] ?? '';
    $department = $_POST['department'] ?? '';
    $subsidy = $_POST['subsidy'] ?? '';
    $status = $_POST['status'] ?? '';
    $locality = $_POST['locality'] ?? '';
    if ($date && $faculty && $title && $department && $subsidy && $status && $locality) {
        $entry = [$date, $faculty, $title, $department, $subsidy, $status, $locality];
        $fp = fopen($data_file, 'a');
        fputcsv($fp, $entry);
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries
$default_entries = [
    ['2025-05-15', 'Dr. Sarah Johnson', 'AI-Driven Healthcare Solutions for Rural Communities', 'Computer Science', '₱75,000', 'Approved', 'International'],
    ['2025-05-10', 'Prof. Michael Chen', 'Sustainable Urban Development Strategies', 'Environmental Engineering', '₱50,000', 'Under Review', 'Local'],
    ['2025-05-08', 'Dr. Emily Rodriguez', 'Digital Learning Platforms in Higher Education', 'Education', '₱35,000', 'Approved', 'International'],
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
  <title>Publications and Presentations</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/publication.css">
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
      <a href="Publication and Presentation.php" class="active">Publications and Presentations</a>
      <a href="KPI records.php">KPI Records</a>
    </nav>
    <button class="login-btn" onclick="window.location.href='loginpage.php'">Login</button>
  </header>
  <div class="dashboard-bg">
    <div class="container">
      <h1>Publications and Presentations</h1>
      <div class="subtitle">Track research funding applications and publication subsidies</div>
      <div class="actions">
        <button class="btn upload">&#8682; Upload Excel File</button>
        <button class="btn add" id="showAddForm">+ Add New Entry</button>
      </div>
      <form class="add-entry-form" id="addEntryForm" method="post" action="" style="display:none; background:#f9f9f9; padding:20px; border-radius:8px; margin-bottom:20px;">
        <label>Date of Application:<br><input type="date" name="date" required></label><br>
        <label>Name of Faculty/Research Worker:<br><input type="text" name="faculty" required></label><br>
        <label>Title of Paper:<br><input type="text" name="title" required></label><br>
        <label>Department:<br><input type="text" name="department" required></label><br>
        <label>Research Subsidy:<br><input type="text" name="subsidy" required></label><br>
        <label>Status:<br>
          <select name="status" required>
            <option value="Approved">Approved</option>
            <option value="Under Review">Under Review</option>
            <option value="Rejected">Rejected</option>
          </select>
        </label><br>
        <label>Local/International:<br>
          <select name="locality" required>
            <option value="Local">Local</option>
            <option value="International">International</option>
          </select>
        </label><br>
        <button type="submit" class="btn">Add Entry</button>
        <button type="button" class="btn" id="cancelAddForm">Cancel</button>
      </form>
      <div class="panel">
        <h2>Publications & Presentations Overview</h2>
        <div class="search-bar-wrapper">
          <span class="search-icon">&#128269;</span>
          <input class="search-bar" type="text" placeholder="Search publications and presentations..." onkeyup="filterTable()">
        </div>
        <div class="table-container">
          <table id="pubTable">
            <thead>
              <tr>
                <th>Date of Application</th>
                <th>Name of Faculty/Research Worker</th>
                <th>Title of Paper</th>
                <th>Department</th>
                <th>Research Subsidy</th>
                <th>Status</th>
                <th>Local/International</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $i => $entry): ?>
              <tr<?php if ($edit_index === $i) echo ' style="background:#ffeeba;"'; ?>>
                <td data-label="Date of Application"><?php echo htmlspecialchars($entry[0]); ?></td>
                <td data-label="Name of Faculty/Research Worker"><?php echo htmlspecialchars($entry[1]); ?></td>
                <td data-label="Title of Paper"><?php echo htmlspecialchars($entry[2]); ?></td>
                <td data-label="Department"><?php echo htmlspecialchars($entry[3]); ?></td>
                <td data-label="Research Subsidy"><strong><?php echo htmlspecialchars($entry[4]); ?></strong></td>
                <td data-label="Status"><span class="status <?php echo strtolower(str_replace(' ', '', $entry[5])); ?>"><?php echo htmlspecialchars($entry[5]); ?></span></td>
                <td data-label="Local/International"><span class="tag <?php echo strtolower($entry[6]) === 'international' ? 'intl' : 'local'; ?>"><?php echo htmlspecialchars($entry[6]); ?></span></td>
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
                    <label>Date of Application:<br><input type="date" name="date" value="<?php echo htmlspecialchars($edit_entry[0]); ?>" required></label><br>
                    <label>Name of Faculty/Research Worker:<br><input type="text" name="faculty" value="<?php echo htmlspecialchars($edit_entry[1]); ?>" required></label><br>
                    <label>Title of Paper:<br><input type="text" name="title" value="<?php echo htmlspecialchars($edit_entry[2]); ?>" required></label><br>
                    <label>Department:<br><input type="text" name="department" value="<?php echo htmlspecialchars($edit_entry[3]); ?>" required></label><br>
                    <label>Research Subsidy:<br><input type="text" name="subsidy" value="<?php echo htmlspecialchars($edit_entry[4]); ?>" required></label><br>
                    <label>Status:<br>
                      <select name="status" required>
                        <option value="Approved" <?php if ($edit_entry[5]==='Approved') echo 'selected'; ?>>Approved</option>
                        <option value="Under Review" <?php if ($edit_entry[5]==='Under Review') echo 'selected'; ?>>Under Review</option>
                        <option value="Rejected" <?php if ($edit_entry[5]==='Rejected') echo 'selected'; ?>>Rejected</option>
                      </select>
                    </label><br>
                    <label>Local/International:<br>
                      <select name="locality" required>
                        <option value="Local" <?php if ($edit_entry[6]==='Local') echo 'selected'; ?>>Local</option>
                        <option value="International" <?php if ($edit_entry[6]==='International') echo 'selected'; ?>>International</option>
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
    function filterTable() {
      var input = document.querySelector('.search-bar');
      var filter = input.value.toLowerCase();
      var table = document.getElementById('pubTable');
      var trs = table.getElementsByTagName('tr');
      for (var i = 1; i < trs.length; i++) {
        var tds = trs[i].getElementsByTagName('td');
        // Only filter data rows (8 columns), skip edit form rows (colspan=8)
        if (tds.length === 8) {
          var show = false;
          for (var j = 0; j < tds.length - 1; j++) { // Exclude Actions column if you want
            if (tds[j].textContent.toLowerCase().indexOf(filter) > -1) {
              show = true;
              break;
            }
          }
          trs[i].style.display = show ? '' : 'none';
          // Also hide the edit form row if its data row is hidden
          if (trs[i + 1] && trs[i + 1].querySelector('.edit-entry-form')) {
            trs[i + 1].style.display = show ? '' : 'none';
          }
        }
      }
    }
  </script>
</body>
</html> 