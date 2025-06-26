<?php
// File to store entries
$data_file = __DIR__ . '/ethics_reviewed_protocols.csv';

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
        $no = $_POST['no'] ?? '';
        $title = $_POST['title'] ?? '';
        $department = $_POST['department'] ?? '';
        $status = $_POST['status'] ?? '';
        $action = $_POST['action'] ?? '';
        if ($no && $title && $department && $status && $action) {
            $entries[$index] = [$no, $title, $department, $status, $action];
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
    $no = $_POST['no'] ?? '';
    $title = $_POST['title'] ?? '';
    $department = $_POST['department'] ?? '';
    $status = $_POST['status'] ?? '';
    $action = $_POST['action'] ?? '';
    if ($no && $title && $department && $status && $action) {
        $entry = [$no, $title, $department, $status, $action];
        $fp = fopen($data_file, 'a');
        fputcsv($fp, $entry);
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries
$default_entries = [
    ['EP-2025-001', 'Impact of AI on Student Privacy in Educational Platforms', 'Computer Science Department', 'Approved', 'Full Ethics Clearance Granted'],
    ['EP-2025-002', 'Mental Health Survey Among University Students', 'Psychology Department', 'Under Review', 'Additional Documentation Requested'],
    ['EP-2025-003', 'Environmental Impact Assessment of Campus Operations', 'Environmental Science Department', 'Approved', 'Conditional Approval with Monitoring'],
    ['EP-2025-004', 'Social Media Usage Patterns Research', 'Sociology Department', 'Pending', 'Initial Review in Progress'],
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
  <title>Ethics Reviewed Protocols</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/ethics reviewed protocols.css">
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
      <a href="Ethicss Reviewed Protocols.php" class="active">Ethics Reviewed Protocols</a>
      <a href="Publication and Presentation.php">Publications and Presentations</a>
      <a href="KPI records.php">KPI Records</a>
    </nav>
    <button class="login-btn" onclick="window.location.href='loginpage.php'">Login</button>
  </header>
  <div class="dashboard-bg">
    <div class="container">
      <h1>Ethics Reviewed Protocols</h1>
      <div class="subtitle">Monitor research ethics compliance and protocol approvals</div>
      <div class="actions">
        <button class="btn upload">&#8682; Upload Excel File</button>
        <button class="btn add" id="showAddForm">+ Add New Entry</button>
      </div>
      <form class="add-entry-form" id="addEntryForm" method="post" action="" style="display:none; background:#f9f9f9; padding:20px; border-radius:8px; margin-bottom:20px;">
        <label>No.:<br><input type="text" name="no" required></label><br>
        <label>List of Title:<br><input type="text" name="title" required></label><br>
        <label>Department/Data Forwarded:<br><input type="text" name="department" required></label><br>
        <label>Status:<br>
          <select name="status" required>
            <option value="Approved">Approved</option>
            <option value="Under Review">Under Review</option>
            <option value="Pending">Pending</option>
          </select>
        </label><br>
        <label>Action Taken:<br><input type="text" name="action" required></label><br>
        <button type="submit" class="btn">Add Entry</button>
        <button type="button" class="btn" id="cancelAddForm">Cancel</button>
      </form>
      <div class="panel">
        <h2>Ethics Protocols Overview</h2>
        <div class="search-bar-wrapper">
          <span class="search-icon">&#128269;</span>
          <input class="search-bar" type="text" placeholder="Search ethics protocols..." onkeyup="filterTable()">
        </div>
        <div class="table-container">
          <table id="ethicsTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>List of Title</th>
                <th>Department/Data Forwarded</th>
                <th>Status</th>
                <th>Action Taken</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $i => $entry): ?>
              <tr<?php if ($edit_index === $i) echo ' style="background:#ffeeba;"'; ?>>
                <td data-label="No."><strong><?php echo htmlspecialchars($entry[0]); ?></strong></td>
                <td data-label="List of Title"><?php echo htmlspecialchars($entry[1]); ?></td>
                <td data-label="Department/Data Forwarded"><?php echo htmlspecialchars($entry[2]); ?></td>
                <td data-label="Status"><span class="status <?php echo strtolower(str_replace(' ', '', $entry[3])); ?>"><?php echo htmlspecialchars($entry[3]); ?></span></td>
                <td data-label="Action Taken"><?php echo htmlspecialchars($entry[4]); ?></td>
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
                <td colspan="6">
                  <form class="edit-entry-form" method="post" action="">
                    <input type="hidden" name="save_edit" value="1">
                    <input type="hidden" name="index" value="<?php echo $edit_index; ?>">
                    <label>No.:<br><input type="text" name="no" value="<?php echo htmlspecialchars($edit_entry[0]); ?>" required></label><br>
                    <label>List of Title:<br><input type="text" name="title" value="<?php echo htmlspecialchars($edit_entry[1]); ?>" required></label><br>
                    <label>Department/Data Forwarded:<br><input type="text" name="department" value="<?php echo htmlspecialchars($edit_entry[2]); ?>" required></label><br>
                    <label>Status:<br>
                      <select name="status" required>
                        <option value="Approved" <?php if ($edit_entry[3]==='Approved') echo 'selected'; ?>>Approved</option>
                        <option value="Under Review" <?php if ($edit_entry[3]==='Under Review') echo 'selected'; ?>>Under Review</option>
                        <option value="Pending" <?php if ($edit_entry[3]==='Pending') echo 'selected'; ?>>Pending</option>
                      </select>
                    </label><br>
                    <label>Action Taken:<br><input type="text" name="action" value="<?php echo htmlspecialchars($edit_entry[4]); ?>" required></label><br>
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
      var table = document.getElementById('ethicsTable');
      var trs = table.getElementsByTagName('tr');
      for (var i = 1; i < trs.length; i++) {
        var tds = trs[i].getElementsByTagName('td');
        var show = false;
        for (var j = 0; j < tds.length; j++) {
          if (tds[j].textContent.toLowerCase().indexOf(filter) > -1) {
            show = true;
            break;
          }
        }
        trs[i].style.display = show ? '' : 'none';
      }
    }
  </script>
</body>
</html> 